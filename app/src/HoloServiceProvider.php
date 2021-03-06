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
use League\Route\Strategy\ApplicationStrategy as RouterApplicationStrategy;
use League\CommonMark\Environment\Environment as CommonMarkEnvironment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\Attributes\AttributesExtension;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\FrontMatter\Data\SymfonyYamlFrontMatterParser;
use League\CommonMark\Extension\FrontMatter\FrontMatterParser;
use League\Flysystem\MountManager;
use League\Flysystem\FileSystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Plates\Engine;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7Server\ServerRequestCreator;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ThematicBreak;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\Table\Table;
use League\CommonMark\Node\Block\Paragraph;

/**
 * Holo Service Provider
 */
class HoloServiceProvider extends AbstractServiceProvider {

    private const SERVICES = [
        Router::class,
        CommonMarkEnvironment::class,
        FrontMatterParser::class,
        MarkownConverter::class,
        MountManager::class,
        EngineClass::class,
        Psr17Factory::class,
        SapiEmitter::class,
        WebController::class,
        WebSupplyMiddleware::class,
    ];

    /**
     * @inherit
     */
    public function provides(string $id): bool
    {
        return in_array($id, self::SERVICES);
    }

    /**
     * @inherit
     */
    public function register(): void
    {
        $container = $this->getContainer();
        
        # router (singleton)
        $container
            ->add(
                Router::class, 
                function() use ($container) {
                    $router = new Router();
                    $strategy = new RouterApplicationStrategy();
                    $strategy->setContainer($container);
                    $router->setStrategy($strategy);

                    return $router;
                }
            )
            ->setShared()
        ;

        # commonmark (singletons)
        $container
            ->add(
                CommonMarkEnvironment::class, 
                function() {
                    $configuration = [];
                    $configuration['default_attributes'] = [
                        Table::class => [
                            'class' => ['ui', 'celled', 'striped', 'table'],
                        ],
                        BlockQuote::class => [
                            'class' => ['ui', 'secondary', 'segment'],
                        ],
                        ListBlock::class => [
                            'class' => ['ui', 'list'],
                        ],
                        Heading::class => [
                            'class' => ['ui', 'heading'],
                        ],
                        ThematicBreak::class => [
                            'class' => ['ui', 'divider'],
                        ],
                        Image::class => [
                            'class' => ['ui', 'rounded', 'bordered', 'image'],
                        ],
                    ];

                    $environment = new CommonMarkEnvironment($configuration);
                    $environment->addExtension(new CommonMarkCoreExtension());
                    $environment->addExtension(new FrontMatterExtension());
                    $environment->addExtension(new DefaultAttributesExtension());
                    $environment->addExtension(new AttributesExtension());
                    return $environment;
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

        # flysystem (singleton for file access)
        $container
            ->add(
                MountManager::class,
                function() {
                    return new MountManager(
                        [
                            # enables "web:" to access markdown files directly by the URL-Paths 
                            # jailed into the provided directory as a root directory
                            "web" => new FileSystem(new LocalFilesystemAdapter(__DIR__.'/../../web')),
                        ]
                    );
                }
            )
            ->setShared()
        ;

        # plates (singleton for templates access)
        $container
            ->add(
                Engine::class,
                function() {
                    $engine = new Engine(__DIR__.'/../../templates');
                    $engine->addFolder('templates', __DIR__.'/../../templates');

                    return $engine;
                }
            )
            ->setShared()
        ;

        # PSR-17 Factories (singleton)
        $container->add(Psr17Factory::class)->setShared();
        $container
            ->add(ServerRequestCreator::class)
            ->addArgument(Psr17Factory::class)
            ->addArgument(Psr17Factory::class)
            ->addArgument(Psr17Factory::class)
            ->addArgument(Psr17Factory::class)
            ->setShared()
        ;
        $container->add(SapiEmitter::class)->setShared();

        # controller
        $container
            ->add(WebController::class)
            ->addArgument(Engine::class)
            ->addArgument(MountManager::class)
            ->addArgument(MarkdownConverter::class)
        ;

        # web supply middleware for route-directory relationships and exception handling
        $container
            ->add(
                WebSupplyMiddleware::class, 
                function() use($container) {
                    return new WebSupplyMiddleware($container);
                }
            )
        ;
    }

} 
