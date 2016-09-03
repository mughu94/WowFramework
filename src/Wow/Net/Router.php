<?php

namespace Wow\Net;

/**
 * The Router class is responsible for routing an HTTP request to
 * an assigned callback function. The Router tries to match the
 * requested URL against a series of URL patterns. 
 */
class Router {
    /**
     * Mapped routes.
     *
     * @var array
     */
    protected $routes = array();

    /**
     * Pointer to current route
     *
     * @var int
     */
    protected $index = 0;

    /**
     * Gets mapped routes.
     *
     * @return array Array of routes
     */
    public function getRoutes() {
        return $this->routes;
    }

    /**
     * Clears all routes in the router.
     */
    public function clear() {
        $this->routes = array();
    }

    /**
     * Maps a URL pattern to a callback function.
     *
     * @param string $pattern URL pattern to match
     * @param array $defaults Default route params
     * @param  bool $case_sensitive Is Case Sensitive
     */
    public function map($pattern, $defaults, $case_sensitive = TRUE) {
        $url = $pattern;
        $methods = array('*');

        if (strpos($pattern, ' ') !== false) {
            list($method, $url) = explode(' ', trim($pattern), 2);

            $methods = explode('|', $method);
        }

        $this->routes[] = new Route($url, $defaults, $methods, $case_sensitive);
    }

    /**
     * Routes the current request.
     *
     * @param Request $request Request object
     * @return bool|Route Matching route
     */
    public function route(Request $request) {
        while ($route = $this->current()) {
            if ($route !== false && $route->matchMethod($request->method) && $route->matchUrl($request->url)) {
                return $route;
            }
            $this->next();
        }

        return false;
    }

    /**
     * Gets the current route.
     *
     * @return Route
     */
    public function current() {
        return isset($this->routes[$this->index]) ? $this->routes[$this->index] : false;
    }

    /**
     * Gets the next route.
     *
     * @return Route
     */
    public function next() {
        $this->index++;
    }

    /**
     * Reset to the first route.
     */
    public  function reset() {
        $this->index = 0;
    }
}

