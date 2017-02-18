<?php

/**
 * The App class is a static representation of the framework.
 * 
 * @method  static void map($name, $callback) Creates a custom framework method.
 * @method  static void register($name, $class, array $params = array(), $callback = null) Registers a class to a framework method.
 * @method  static void before($name, $callback) Adds a filter before a framework method.
 * @method  static void after($name, $callback) Adds a filter after a framework method.
 * @method  static void path($path) Adds a path for autoloading classes.
 * @method  static mixed get($key) Gets a variable.
 * @method  static void set($key, $value) Sets a variable.
 * @method  static bool has($key) Checks if a variable is set.
 * @method  static void clear($key = null) Clears a variable.
 * @method  static void start() Starts the framework.
 * @method  static void error($exception) Sends an HTTP 500 response.
 */
class Wow {
    /**
     * Framework engine.
     *
     * @var object
     */
    private static $engine;

    // Don't allow object instantiation
    private function __construct() {}
    private function __destruct() {}
    private function __clone() {}

    /**
     * Handles calls to static methods.
     *
     * @param string $name Method name
     * @param array $params Method parameters
     * @return mixed Callback results
     */
    public static function __callStatic($name, $params) {
        $app = Wow::app();

        return \Wow\Core\Dispatcher::invokeMethod(array($app, $name), $params);
    }

    /**
     * @return object Application instance
     */
    public static function app() {
        static $initialized = false;

        if (!$initialized) {

            self::$engine = new \Wow\Engine();

            $initialized = true;
        }

        return self::$engine;
    }
}
