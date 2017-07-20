<?php

namespace Rareloop\WordPress\Router;

use Rareloop\Router\Route;
use Rareloop\Router\Router as RareRouter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Router
{
    protected static $singleton;

    /**
     * Initialise the Router
     *
     * @return void
     */
    public static function init()
    {
        $router = static::instance();

        // Infer the base path from the site's URL
        $siteUrl = get_bloginfo('url');
        $siteUrlParts = explode('/', rtrim($siteUrl, ' //'));
        $siteUrlParts = array_slice($siteUrlParts, 3);
        $basePath = implode('/', $siteUrlParts);

        if (!$basePath) {
            $basePath = '/';
        } else {
            $basePath = '/' . $basePath . '/';
        }

        $router->setBasePath($basePath);

        // Give a chance for the outside app to modify the Router object post configuration
        $router = static::$singleton = apply_filters('rareloop_router_configured', $router);

        // Listen for when we should check whether any defined routes match
        add_action('wp_loaded', [static::class, 'processRequest']);
    }

    /**
     * Attempt to match the current request against the defined routes
     *
     * If a route matches the Response will be sent to the client and PHP will exit.
     *
     * @return void
     */
    public static function processRequest()
    {
        $request = Request::createFromGlobals();
        $response = static::match($request);

        if ($response->getStatusCode() === 404) {
            return;
        }

        $response->send();
        static::shutdown();
    }

    /**
     * Shutdown PHP
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected static function shutdown()
    {
        exit();
    }

    /**
     * Get the singleton instance of the Router
     *
     * @return Rareloop\Router\Router
     */
    private static function instance() : RareRouter
    {
        if (!isset(static::$singleton)) {
            static::$singleton = apply_filters('rareloop_router_created', new RareRouter);
        }

        return static::$singleton;
    }

    /**
     * Match the provided Request against the defined routes and return a Response
     *
     * @param  Symfony\Component\HttpFoundation\Request $request
     * @return Symfony\Component\HttpFoundation\Response
     */
    public static function match(Request $request) : Response
    {
        return static::instance()->match($request);
    }

    /**
     * Map a route
     *
     * @param  array  $verbs
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function map(array $verbs, string $uri, $callback): Route
    {
        return static::instance()->map($verbs, $uri, $callback);
    }

    /**
     * Map a route using the GET method
     *
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function get(string $uri, $callback) : Route
    {
        return static::instance()->get($uri, $callback);
    }

    /**
     * Map a route using the POST method
     *
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function post(string $uri, $callback) : Route
    {
        return static::instance()->post($uri, $callback);
    }

    /**
     * Map a route using the PATCH method
     *
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function patch(string $uri, $callback) : Route
    {
        return static::instance()->patch($uri, $callback);
    }

    /**
     * Map a route using the PUT method
     *
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function put(string $uri, $callback) : Route
    {
        return static::instance()->put($uri, $callback);
    }

    /**
     * Map a route using the DELETE method
     *
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function delete(string $uri, $callback) : Route
    {
        return static::instance()->delete($uri, $callback);
    }

    /**
     * Map a route using the OPTIONS method
     *
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public static function options(string $uri, $callback) : Route
    {
        return static::instance()->options($uri, $callback);
    }

    /**
     * Create a Route group
     *
     * @param  string $prefix
     * @param  callable $callback
     * @return Rareloop\Router\Router
     */
    public static function group(string $prefix, $callback) : RareRouter
    {
        return static::instance()->group($prefix, $callback);
    }

    /**
     * Get the URL for a named route
     *
     * @param  string $name
     * @param  array  $params
     * @return string
     */
    public static function url(string $name, $params = [])
    {
        return static::instance()->url($name, $params);
    }
}
