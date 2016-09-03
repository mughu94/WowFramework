<?php

    namespace Wow\Core;

    use ReflectionMethod;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;

    /**
     * The Dispatcher class is responsible for dispatching events. Events
     * are simply aliases for class methods or functions. The Dispatcher
     * allows you to hook other functions to an event that can modify the
     * input parameters and/or the output.
     */
    class Dispatcher {
        /**
         * Mapped events.
         *
         * @var array
         */
        protected $events = array();

        /**
         * Method filters.
         *
         * @var array
         */
        protected $filters = array();

        /**
         * Dispatches an event.
         *
         * @param string $name   Event name
         * @param array  $params Callback parameters
         *
         * @return string Output of callback
         */
        public function run($name, array $params = array()) {
            $output = '';

            // Run pre-filters
            if(!empty($this->filters[$name]['before'])) {
                $this->filter($this->filters[$name]['before'], $params, $output);
            }

            // Run requested method
            $output = $this->execute($this->get($name), $params);

            // Run post-filters
            if(!empty($this->filters[$name]['after'])) {
                $this->filter($this->filters[$name]['after'], $params, $output);
            }

            return $output;
        }

        /**
         * Assigns a callback to an event.
         *
         * @param string   $name     Event name
         * @param callback $callback Callback function
         */
        public function set($name, $callback) {
            $this->events[$name] = $callback;
        }

        /**
         * Gets an assigned callback.
         *
         * @param string $name Event name
         *
         * @return callback $callback Callback function
         */
        public function get($name) {
            return isset($this->events[$name]) ? $this->events[$name] : NULL;
        }

        /**
         * Checks if an event has been set.
         *
         * @param string $name Event name
         *
         * @return bool Event status
         */
        public function has($name) {
            return isset($this->events[$name]);
        }

        /**
         * Clears an event. If no name is given,
         * all events are removed.
         *
         * @param string $name Event name
         */
        public function clear($name = NULL) {
            if($name !== NULL) {
                unset($this->events[$name]);
                unset($this->filters[$name]);
            } else {
                $this->events  = array();
                $this->filters = array();
            }
        }

        /**
         * Hooks a callback to an event.
         *
         * @param string   $name     Event name
         * @param string   $type     Filter type
         * @param callback $callback Callback function
         */
        public function hook($name, $type, $callback) {
            $this->filters[$name][$type][] = $callback;
        }

        /**
         * Executes a chain of method filters.
         *
         * @param array $filters Chain of filters
         * @param array $params  Method parameters
         * @param mixed $output  Method output
         */
        public function filter($filters, &$params, &$output) {
            $args = array(
                &$params,
                &$output
            );
            foreach($filters as $callback) {
                $continue = $this->execute($callback, $args);
                if($continue === FALSE) {
                    break;
                }
            }
        }

        /**
         * Executes a callback function.
         *
         * @param callback $callback Callback function
         * @param array    $params   Function parameters
         *
         * @return mixed Function results
         * @throws \Exception
         */
        public static function execute($callback, array &$params = array()) {
            if(is_callable($callback)) {
                return is_array($callback) ? self::invokeMethod($callback, $params) : self::callFunction($callback, $params);
            } else {
                throw new \Exception('Invalid callback specified.');
            }
        }

        /**
         * Calls a function.
         *
         * @param string $func   Name of function to call
         * @param array  $params Function parameters
         *
         * @return mixed Function results
         */
        public static function callFunction($func, array &$params = array()) {
            switch(count($params)) {
                case 0:
                    return $func();
                case 1:
                    return $func($params[0]);
                case 2:
                    return $func($params[0], $params[1]);
                case 3:
                    return $func($params[0], $params[1], $params[2]);
                case 4:
                    return $func($params[0], $params[1], $params[2], $params[3]);
                case 5:
                    return $func($params[0], $params[1], $params[2], $params[3], $params[4]);
                default:
                    return call_user_func_array($func, $params);
            }
        }

        /**
         * Invokes a method.
         *
         * @param mixed $func   Class method
         * @param array $params Class method parameters
         *
         * @return mixed Function results
         */
        public static function invokeMethod($func, array &$params = array()) {
            list($class, $method) = $func;
            $instance = is_object($class);

            switch(count($params)) {
                case 0:
                    return ($instance) ? $class->$method() : $class::$method();
                case 1:
                    return ($instance) ? $class->$method($params[0]) : $class::$method($params[0]);
                case 2:
                    return ($instance) ? $class->$method($params[0], $params[1]) : $class::$method($params[0], $params[1]);
                case 3:
                    return ($instance) ? $class->$method($params[0], $params[1], $params[2]) : $class::$method($params[0], $params[1], $params[2]);
                case 4:
                    return ($instance) ? $class->$method($params[0], $params[1], $params[2], $params[3]) : $class::$method($params[0], $params[1], $params[2], $params[3]);
                case 5:
                    return ($instance) ? $class->$method($params[0], $params[1], $params[2], $params[3], $params[4]) : $class::$method($params[0], $params[1], $params[2], $params[3], $params[4]);
                default:
                    return call_user_func_array($func, $params);
            }
        }

        /**
         * Executes Route
         *
         * @param Route   $route
         * @param Request $request
         *
         * @return bool|mixed
         */
        public static function executeRoute(Route $route, Request $request) {
            /**
             * @var Controller $ControllerClass
             */

            //Fix for autoloaders case sensivity.
            $fixedViewName   = implode("-", array_map("strtolower", explode("-", $route->params["controller"]))) . "/" . implode("-", array_map("strtolower", explode("-", $route->params["action"])));
            $fixedClassName  = implode("", array_map("ucfirst", array_map("strtolower", explode("-", $route->params["controller"]))));
            $psr4ClassName   = "App\\Controllers\\" . $fixedClassName . "Controller";
            $fixedMethodName = implode("", array_map("ucfirst", array_map("strtolower", explode("-", $route->params["action"]))));
            if(!class_exists($psr4ClassName)) {
                return FALSE;
            } elseif(!method_exists($psr4ClassName, $fixedMethodName . "Action") || !is_callable($psr4ClassName, $fixedMethodName . "Action")) {
                return FALSE;
            }

            $objRefMethod = new ReflectionMethod($psr4ClassName, $fixedMethodName . "Action");
            if($psr4ClassName !== $objRefMethod->class || $objRefMethod->name !== $fixedMethodName . "Action") {
                return FALSE;
            }

            $methodValues = $route->params;
            $methodParams = $objRefMethod->getParameters();

            unset($methodValues["controller"]);
            unset($methodValues["action"]);

            foreach($methodParams as $param) {
                if(!in_array($param->getName(), array_keys($methodValues))) {
                    if(isset($request->query[$param->getName()])) {
                        $methodValues[$param->getName()] = $request->query[$param->getName()];
                    } else {
                        if(!$param->isOptional()) {
                            return FALSE;
                            break;
                        }
                    }
                }
            }


            $route->params["controller"] = $fixedClassName;
            $route->params["action"]     = $fixedMethodName;
            $ControllerClass             = new $psr4ClassName($route, $request);

            return $ControllerClass->init($fixedMethodName . "Action", $fixedViewName, $methodValues);

        }

        /**
         * Resets the object to the initial state.
         */
        public function reset() {
            $this->events  = array();
            $this->filters = array();
        }
    }
