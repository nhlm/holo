<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */
namespace Holo;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Route\Router;
use League\CommonMark\Environment\Environment as CommonMarkEnvironment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use League\Flysystem\MountManager;
use League\Flysystem\FileSystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Plates\Engine;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;


class HoloServiceProvider extends AbstractServiceProvider {

    private const SERVICES = [
        Router::class,

    ];

    public function provides(string $id): bool
    {
        return in_array($id, self::SERVICES);
    }

    public function register(): void
    {
        $container = $this->getContainer();
        
        # router
        $container->add(Router::class)->setShared();

        # commonmark
        $container
            ->add(
                CommonMarkEnvironment::class, 
                function() {
                    $configuration = [];
                    $configuration['default_attributes'] = include(__DIR__.'/../../settings/semantic-ui.php');

                    $environment = new CommonmMarkEnvironment($configuration);
                    $environment->addExtension(new CommonMarkCoreExtension());
                    $environment->addExtension(new FrontMatterExtension());
                    $environment->addExtension(new DefaultAttributesExtension());
                }
            )
            ->setShared()
        ;

        $container
            ->add(
                FrontMatterParser::class,
                function() {
                    return new FrontMatterParser(new SymfonyYamlFrontMatterParser());
                }
            )
            ->setShared()
        ;

        $container
            ->add(MarkdownConverter::class)
            ->addArgument(CommonMarkEnvironment::class)
            ->setShared()
        ;

        # flysystem (file access)
        $container
            ->add(
                League\Flysystem\MountManager::class,
                function() {
                    return new MountManager(
                        [
                            # enables "web:" to access markdown files directly by the URL-Paths 
                            # jailed into the provided directory as a root directory
                            "web" => new FileSystem(new LocalFilesystemAdapter(__DIR__.'/../../web')),

                            # enables "template:" to access templates files
                            "template" => new FileSystem(new LocalFilesystemAdapter(__DIR__.'/../../templates')),
                        ]
                    );
                }
            )
            ->setShared()
        ;

        # plates
        $container
            ->add(
                Engine::class,
                function() use ($container) {
                    $templates = new Engine(__DIR__.'/../../templates');
                    $templates->addData(
                        [
                            'request' => $container->get(Request::class),
                        ]
                    );

                    return $templates;
                }
            )
            ->setShared()
        ;
    }

} 
