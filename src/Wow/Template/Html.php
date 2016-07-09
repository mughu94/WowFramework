<?php

    namespace Wow\Template;

    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;
    use Wow\Core\Dispatcher;

    /**
     * Class Html
     * @package Wow\Template
     */

    class Html {
        /**
         * Html constructor.
         *
         * @param Request  $request
         */
        public function __construct(Request $request) {
            $this->route    = new Route("*", array(
                "controller" => "",
                "action"     => ""
            ), array("GET"));
            $this->request  = $request;
            $this->dispatcher = new Dispatcher();
        }

        /**
         * Executes An Action
         *
         * @param string $controller
         * @param string $action
         * @param array  $routeParams
         *
         * @return bool if Controller Class Not Exists
         */
        public function action($controller, $action = "Index", $routeParams = array()) {
            /**
             * @var Response $content
             */
            $this->route->params = array_merge($this->route->params, $routeParams);
            $this->route->params["controller"] = $controller;
            $this->route->params["action"] = $action;
            $content = $this->dispatcher->executeRoute($this->route, $this->request);
            if(!$content){
                return FALSE;
            }
            else{
                echo $content->getBody();
            }
        }
    }