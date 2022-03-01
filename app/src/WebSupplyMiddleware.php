<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */
namespace Holo;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use League\Flysystem\MountManager;
use Holo\Exception\HttpException;
use Holo\Exception\NotFoundException;
use League\Plates\Engine;
use Nyholm\Psr7\Response;

class WebSupplyMiddleware implements MiddlewareInterface {

    protected ContainerInterface $container;

    /**
     * Constructor.
     * 
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        
        $file = $path.($path[-1] === "/" ? 'index.md' : '.md');
        $filePath = 'web://'.$file;

        try {
            if ( ! $this->container->get(MountManager::class)->fileExists($filePath) ) {
                throw new NotFoundException(404, "Requested entity not found.");
            }

            return $handler->handle($request);
        }
        catch ( HttpException $exception ) {
            $engine = $this->container->get(Engine::class);
            $response = new Response($exception->getStatusCode());

            if ( ! $engine->exists('templates::error') ) {
                $response->getBody()->write(
                    sprintf(
                        '%s (%d) raised and no error template was provided: %s',
                        get_class($boo),
                        $exception->getCode(),
                        $exception->getMessage()
                    )
                );

                return $response;
            }

            $output = $engine->render(
                'templates::error', 
                [
                    'message' => $exception->getMessage(),
                    'code' => $exception->getCode(),
                    'interface' => $exception::class,
                    'path' => $path,
                ]
            );

            $response->getBody()->write($output);

            return $response;
        }

    }

}