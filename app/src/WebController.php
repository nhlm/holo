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

class WebController {

    /** @var Engine holds the template engine object */
    protected Engine $templates;

    /** @var MountManager holds the mount manager for filesystem access */
    protected MountManager $filesystem;

    /**
     * Constructor.
     * 
     * @param Engine $templates
     * @param MountManager $filesystem
     */
    public function __construct(Engine $templates, MountManager $filesystem)
    {
        $this->templates = $templates;
        $this->filesystem = $filesystem;
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

    }

}
