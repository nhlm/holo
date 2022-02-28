<?php declare(strict_types=1);
/**
 * Holo Project
 * 
 * (c) 2022 Matthias "nihylum" Kaschubowski
 * 
 * @package holo
 */

require(__DIR__.'/../vendor/autoload.php');

/**
 * PHP in-build development server static files gateway.
 */
if ( php_sapi_name() == 'cli-server' && file_exists(__DIR__.'/'.parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)) ) {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ( ! empty($path) && $path !== "/" ) {
        return false;
    }
}

/**
 * the application wrapper.
 */
$app = new Holo\Holo();

/**
 * bootstrapping the application.
 * 
 * This function call may receive a custom callback. The callback will be called
 * with the dependency container instance as its only argument to add additionally
 * services or to modifiy registered services.
 * 
 * Singletons which can be modified by fetching them from the container are:
 * - League\Route\Router (the router)
 * - League\CommonMark\Environment\Environment (the commommark environment)
 * - League\CommonMark\MarkdownConverter (the markdown converter used at the web controller of Holo)
 * - League\CommonMark\Extension\FrontMatter\FrontMatterParser (the frontmatter parser of commonmark)
 * - League\Flysystem\MountManager (the flysystem mount manager)
 * - League\Plates\Engine (the plates template engine engine-object)
 * 
 * The catch all route for the incoming request will be registered to the router after the optionally
 * provided callback is processed. You may add static routes here to implement something.
 */
$app->bootstrap();

/**
 * starts the application process to turn the request into a response.
 */
$app->run();
