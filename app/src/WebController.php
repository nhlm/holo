<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */
namespace Holo;

use League\Plates\Engine;
use League\Flysystem\MountManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Nyholm\Psr7\Response;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Extension\FrontMatter\Output\RenderedContentWithFrontMatter;

class WebController {

    /** @var Engine holds the template engine object */
    protected Engine $templates;

    /** @var MountManager holds the mount manager for filesystem access */
    protected MountManager $filesystem;

    /** @var MarkdownConverter hols the markdown converter */
    protected MarkdownConverter $markdown;

    /**
     * Constructor.
     * 
     * @param Engine $templates
     * @param MountManager $filesystem
     */
    public function __construct(Engine $templates, MountManager $filesystem, MarkdownConverter $markdown)
    {
        $this->templates = $templates;
        $this->filesystem = $filesystem;
        $this->markdown = $markdown;
    }

    /**
     * Controller Action.
     * 
     * @param ServerRequestInterface $request
     * @param string[] $routeArgs
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, array $routeArgs): ResponseInterface
    {
        $path = '/'.($routeArgs['path'] ?? '');
        $file = $path.($path[-1] === "/" ? 'index.md' : '.md');

        $result = $this->markdown->convert($this->filesystem->read('web://'.$file));

        if ( ! $result instanceof RenderedContentWithFrontMatter ) {
            throw new NoFrontmatterException(500, 'Content-File has no Frontmatter.');
        }

        $frontmatter = $result->getFrontmatter();

        if ( array_key_exists('redirect', $frontmatter) ) {
            return new Response(301, ['location' => $frontmatter['redirect']]);
        }

        $frontmatter['content'] = $result->getContent();
        $frontmatter['path'] = $path;

        if ( ! $this->templates->exists($frontmatter['template']) ) {
            throw new UnknownTemplateException(500, sprintf('Template "%s" unknown.', $frontmatter['template']));
        }

        $response = new Response(200);
        $response->getBody()->write($this->templates->render($frontmatter['template'], $frontmatter));
        
        return $response;
    }

}
