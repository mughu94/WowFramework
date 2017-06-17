<?php

    namespace Wow\Net;

    /**
     * The Route class is responsible for routing an HTTP request to
     * an assigned callback function. The Router tries to match the
     * requested URL against a series of URL patterns.
     */
    class Route {
        /**
         * @var string URL pattern
         */
        public $pattern;

        /**
         * @var mixed Default params
         */
        public $defaults;

        /**
         * @var array HTTP methods
         */
        public $methods = array();

        /**
         * @var array Route parameters
         */
        public $params = array();

        /**
         * @var bool Is Route's Url Matching Case Sensitive
         */
        public $case_sensitive;

        /**
         * @var array
         */
        protected $fixedNames = array();


        /**
         * Constructor.
         *
         * @param string $pattern  URL pattern
         * @param mixed  $defaults Default route params
         * @param array  $methods  HTTP methods
         */
        public function __construct($pattern, $defaults, $methods, $case_sensitive = TRUE) {
            $this->pattern        = $pattern;
            $this->defaults       = $defaults;
            $this->methods        = $methods;
            $this->case_sensitive = $case_sensitive;
        }

        /**
         * Checks if a URL matches the route pattern. Also parses named parameters in the URL.
         *
         * @param string  $url            Requested URL
         * @param boolean $case_sensitive Case sensitive matching
         *
         * @return boolean Match status
         */
        public function matchUrl($url) {
            // Wildcard or exact match
            if($this->pattern === '*' || $this->pattern === $url) {
                $this->setFixedNames();

                return TRUE;
            }

            $ids       = array();
            $last_char = substr($this->pattern, -1);

            // Get splat
            if($last_char === '*') {
                $n     = 0;
                $len   = strlen($url);
                $count = substr_count($this->pattern, '/');

                for($i = 0; $i < $len; $i++) {
                    if($url[$i] == '/') {
                        $n++;
                    }
                    if($n == $count) {
                        break;
                    }
                }

            }

            // Build the regex for matching
            $regex = str_replace(array(
                                     ')',
                                     '/*'
                                 ), array(
                                     ')?',
                                     '(/?|/.*?)'
                                 ), $this->pattern);

            $regex = preg_replace_callback('#@([\w]+)(:\{[^/\(\)]*?\})?#', function($matches) use (&$ids) {
                $ids[$matches[1]] = NULL;
                if(isset($matches[3])) {
                    return '(?P<' . $matches[1] . '>' . $matches[3] . ')';
                }

                return '(?P<' . $matches[1] . '>[^/\?]+)';
            }, $regex);

            // Fix trailing slash
            if($last_char === '/') {
                $regex .= '?';
            } // Allow trailing slash
            else {
                $regex .= '/?';
            }

            // Attempt to match route and named parameters
            if(preg_match('#^' . $regex . '(?:\?.*)?$#' . (($this->case_sensitive) ? '' : 'i'), $url, $matches)) {
                foreach($ids as $k => $v) {
                    if(array_key_exists($k, $matches)) {
                        $this->params[$k] = urldecode($matches[$k]);
                    }
                }

                return TRUE;
            }

            return FALSE;
        }

        /**
         * Checks if an HTTP method matches the route methods.
         *
         * @param string $method HTTP method
         *
         * @return bool Match status
         */
        public function matchMethod($method) {
            return count(array_intersect(array(
                                             $method,
                                             '*'
                                         ), $this->methods)) > 0;
        }

        /**
         * Fix for autoloaders case sensivity.
         */
        private function setFixedNames() {
            $fixedClassName = (isset($this->defaults["prefix"]) && !empty($this->defaults["prefix"])) ? $this->defaults["prefix"] . '/' : '';
            if(isset($this->params["controller"])) {
                $explodeFold = explode("/", $this->params["controller"]);
                for($i = 0; $i < count($explodeFold); $i++) {
                    $fixedClassName .= implode("", array_map("ucfirst", array_map("strtolower", explode("-", $explodeFold[$i]))));
                    $fixedClassName .= $i == (count($explodeFold) - 1) ? '' : '/';
                }
            } else {
                $fixedClassName .= $this->defaults["controller"];
            }
            $fixedMethodName = isset($this->params["action"]) ? implode("", array_map("ucfirst", array_map("strtolower", explode("-", $this->params["action"])))) : $this->defaults["action"];


            $this->fixedNames = array(
                "className"  => $fixedClassName,
                "methodName" => $fixedMethodName
            );
            //Merge with defaults
            $this->params = array_merge($this->defaults, $this->params);
            //Prefix is not a param. So remove it.
            unset($this->params["prefix"]);
        }

        /**
         * Get fixed names
         */
        public function getFixedNames() {
            if(empty($this->fixedNames)) {
                $this->setFixedNames();
            }

            return $this->fixedNames;
        }
    }
