<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */
namespace Holo;

use League\Container\Container as DepdenencyContainer;
use League\Route\Router;
use Nyholm\Psr7Server\ServerRequestCreator;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

/**
 * Holo Application Wrapper Class
 */
class Holo {

    /**
     * Constructor.
     * 
     * @param DependencyContainer|null $container
     */
    public function __construct(DependencyContainer $container = null)
    {
        $this->container = $container ?? new DependencyContainer();
    }

    /**
     * Bootstrapper.
     * 
     * @param callable|null $callback
     * @return void
     */
    public function bootstrap(callable $callback = null): void
    {
        $this->container->addServiceProvider(new HoloServiceProvider());

        if ( $callback !== null ) {
            $callback($this->container);
        }
        
        $this->container->get(Router::class)->map('GET', '/{path:.*}', WebController::class);
    }

    /**
     * Application exection.
     * 
     * @return void
     */
    public function run(): void
    {
        $request = $this->container->get(ServerRequestCreator::class)->fromGlobals();
        $response = $this->container->get(Router::class)->dispatch($request);
        $this->container->get(SapiEmitter::class)->emit($response);
    }

}
