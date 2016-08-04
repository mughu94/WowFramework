<?php
    namespace Wow;

    use ErrorException;
    use Exception;
    use Wow\Core\Dispatcher;
    use Wow\Core\Loader;
    use Wow\Net\Request;
    use Wow\Net\Router;
    use Wow\Template\View;

    /**
     * The Engine class contains the Core functionality of the framework.
     * It is responsible for loading an HTTP request, running the assigned services,
     * and generating an HTTP response.
     */
    class Engine {
        /**
         * Stored variables.
         *
         * @var array
         */
        protected $vars;

        /**
         * Class loader.
         *
         * @var object
         */
        protected $loader;

        /**
         * Event dispatcher.
         *
         * @var object
         */
        protected $dispatcher;

        /**
         * Return response
         *
         * @var object
         */
        protected $response;

        /**
         * Constructor.
         */
        public function __construct() {
            // Start Session
            session_start();

            $this->vars = array();

            $this->loader     = new Loader();
            $this->dispatcher = new Dispatcher();
            $this->router     = new Router();
            $this->request    = new Request();


            $this->init();
        }

        /**
         * Destructor
         */
        public function __destruct() {

        }

        /**
         * Handles calls to class methods.
         *
         * @param string $name   Method name
         * @param array  $params Method parameters
         *
         * @return mixed Callback results
         */
        public function __call($name, $params) {
            $callback = $this->dispatcher->get($name);

            if(is_callable($callback)) {
                return $this->dispatcher->run($name, $params);
            }

            $shared = (!empty($params)) ? (bool)$params[0] : TRUE;

            return $this->loader->load($name, $shared);
        }

        /*** Core Methods ***/

        /**
         * Initializes the framework.
         */
        public function init() {
            static $initialized = FALSE;

            if($initialized) {
                $this->vars = array();
                $this->loader->reset();
                $this->dispatcher->reset();
            }

            // Register framework methods
            $methods = array(
                'start',
                'stop',
                'error'
            );
            foreach($methods as $name) {
                $this->dispatcher->set($name, array(
                    $this,
                    '_' . $name
                ));
            }

            // Default configuration settings from config file
            $myConfigArray = include __DIR__ . "/../../app/Config/Config.php";
            foreach($myConfigArray as $key => $value) {
                foreach($value as $item => $val) {
                    $this->set($key . "." . $item, $val);
                }

            }

            // Route configuration from routes file
            $myRoutesArray = include __DIR__ . "/../../app/Config/Routes.php";
            foreach($myRoutesArray as $item => $val) {
                $this->router->map($val[0], $val[1], count($val) > 2 ? $val[2] : $this->get('app.router_case_sensitive'));
            }


            $initialized = TRUE;


        }

        /**
         * Enables/disables custom error handling.
         *
         * @param bool $enabled True or false
         */
        public function handleErrors($enabled) {
            if($enabled) {
                set_error_handler(array(
                                      $this,
                                      'handleError'
                                  ));
                set_exception_handler(array(
                                          $this,
                                          'handleException'
                                      ));
            } else {
                restore_error_handler();
                restore_exception_handler();
            }
        }

        /**
         * Custom error handler. Converts errors into exceptions.
         *
         * @param int $errno   Error number
         * @param int $errstr  Error string
         * @param int $errfile Error file name
         * @param int $errline Error file line number
         *
         * @throws ErrorException
         */
        public function handleError($errno, $errstr, $errfile, $errline) {
            if($errno & error_reporting()) {
                throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
            }
        }

        /**
         * Custom exception handler. Logs exceptions.
         *
         * @param Exception $e Thrown exception
         */
        public function handleException(Exception $e) {
            if($this->get('app.log_errors')) {
                error_log($e->getMessage());
            }

            $this->error($e);
        }

        /**
         * Maps a callback to a framework method.
         *
         * @param string   $name     Method name
         * @param callback $callback Callback function
         *
         * @throws Exception If trying to map over a framework method
         */
        public function map($name, $callback) {
            if(method_exists($this, $name)) {
                throw new Exception('Cannot override an existing framework method.');
            }

            $this->dispatcher->set($name, $callback);
        }

        /**
         * Registers a class to a framework method.
         *
         * @param string   $name     Method name
         * @param string   $class    Class name
         * @param array    $params   Class initialization parameters
         * @param callback $callback Function to call after object instantiation
         *
         * @throws Exception If trying to map over a framework method
         */
        public function register($name, $class, array $params = array(), $callback = NULL) {
            if(method_exists($this, $name)) {
                throw new Exception('Cannot override an existing framework method.');
            }

            $this->loader->register($name, $class, $params, $callback);
        }

        /**
         * Adds a pre-filter to a method.
         *
         * @param string   $name     Method name
         * @param callback $callback Callback function
         */
        public function before($name, $callback) {
            $this->dispatcher->hook($name, 'before', $callback);
        }

        /**
         * Adds a post-filter to a method.
         *
         * @param string   $name     Method name
         * @param callback $callback Callback function
         */
        public function after($name, $callback) {
            $this->dispatcher->hook($name, 'after', $callback);
        }

        /**
         * Gets a variable.
         *
         * @param string $key Key
         *
         * @return mixed
         */
        public function get($key = NULL) {
            if($key === NULL) {
                return $this->vars;
            }

            return isset($this->vars[$key]) ? $this->vars[$key] : NULL;
        }

        /**
         * Sets a variable.
         *
         * @param mixed  $key   Key
         * @param string $value Value
         */
        public function set($key, $value = NULL) {
            if(is_array($key) || is_object($key)) {
                foreach($key as $k => $v) {
                    $this->vars[$k] = $v;
                }
            } else {
                $this->vars[$key] = $value;
            }
        }

        /**
         * Checks if a variable has been set.
         *
         * @param string $key Key
         *
         * @return bool Variable status
         */
        public function has($key) {
            return isset($this->vars[$key]);
        }

        /**
         * Unsets a variable. If no key is passed in, clear all variables.
         *
         * @param string $key Key
         */
        public function clear($key = NULL) {
            if(is_null($key)) {
                $this->vars = array();
            } else {
                unset($this->vars[$key]);
            }
        }

        /**
         * Adds a path for class autoloading.
         *
         * @param string $dir Directory path
         */
        public function path($dir) {
            $this->loader->addDirectory($dir);
        }

        /*** Extensible Methods ***/

        /**
         * Starts the framework.
         */
        public function _start() {
            $dispatched = FALSE;
            $self       = $this;
            $router     = $this->router;
            $request    = $this->request;

            // No output before app starts!
            if(ob_get_length() > 0) {
                ob_get_clean();
            }

            // Enable output buffering
            ob_start();


            // Enable error handling
            $this->handleErrors($this->get('app.handle_errors'));


            // Allow post-filters to run
            $this->after('start', function() use ($self) {
                $self->stop();
            });

            // Route the request
            while($route = $router->route($request)) {

                $continue = $this->dispatcher->executeRoute($route, $request);

                if($continue !== FALSE) {
                    $this->response = $continue;
                    $dispatched     = TRUE;
                    break;
                }

                $router->next();

                $dispatched = FALSE;
            }

            if(!$dispatched) {
                $objErrorView   = new View($this->request);
                $this->response = $objErrorView->getContent('error/404', NULL, TRUE);
                $this->response->status(404);
            }
        }

        /**
         * Stops the framework and outputs the current response.
         */
        public function _stop() {
            $this->response->send();
            $output = ob_get_clean();
            exit($output);
        }


        /**
         * Sends an HTTP 500 response for any errors.
         *
         * @param Exception $e
         */
        public function _error(Exception $e) {
            $msg = sprintf('<h1>500 Internal Server Error</h1>' . '<h3>%s (%s)</h3>' . '<pre>%s</pre>', $e->getMessage(), $e->getCode(), $e->getTraceAsString());
            try {
                ob_end_clean();
                ob_start();
                $objErrorView = new View($this->request);
                $response     = $objErrorView->getContent('error/500', array('error' => $e), TRUE);
                $response->send();
                $output = ob_get_clean();
                exit($output);
            } catch(Exception $ex) {
                exit($msg);
            }

        }


    }
