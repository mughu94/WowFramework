<?php

    namespace Wow\Core;

    use Wow;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;
    use Wow\Template\View;

    class Controller {

        public $route;

        function __construct(Route $route, Request $request) {
            $this->route    = $route;
            $this->request  = $request;
            $this->response = new Response();
            $this->view     = new View($request);

            $this->onStart();

            //return $this->init();
        }

        function __destruct() {
            $this->onEnd();
        }

        public function onStart() {

        }

        public function onEnd() {

        }

        /**
         * Inits Controller Method by route params
         *
         * @return mixed
         */
        function init() {
            if(!method_exists($this, $this->route->params["action"]) || !is_callable(array(
                                                                                         $this,
                                                                                         $this->route->params["action"]
                                                                                     ))
            ) {
                return $this->notFound();
            }
            $routeValues = $this->route->params;
            unset($routeValues["controller"]);
            unset($routeValues["action"]);

            return call_user_func_array(array(
                                            $this,
                                            $this->route->params["action"]
                                        ), $routeValues);
        }

        /**
         * Renders View
         *
         * @param string $data
         * @param string $viewname
         *
         * @return Response
         */
        public function view($data = NULL, $viewname = NULL) {
            if($viewname == NULL) {
                $viewname = $this->route->params["controller"] . "/" . $this->route->params["action"];
            }

            return $this->view->getContent($viewname, $data);
        }

        /**
         * Renders Partial View
         *
         * @param mixed  $data
         * @param string $viewname
         *
         * @return Response
         */
        public function partialView($data = NULL, $viewname = NULL) {
            if($viewname == NULL) {
                $viewname = $this->route->params["controller"] . "/" . $this->route->params["action"];
            }

            return $this->view->getContent($viewname, $data, TRUE);
        }


        /**
         * Renders data as json object
         *
         * @param array $data
         * @param bool  $encode
         *
         * @return Response
         */
        public function json($data, $encode = TRUE) {
            $json = ($encode) ? json_encode($data) : $data;

            $this->response->clear()
                           ->status(200)
                           ->header('Content-Type', 'application/json')
                           ->write($json);

            return $this->response;
        }

        /**
         * Renders data as jsonp
         *
         * @param array  $data
         * @param string $param
         * @param bool   $encode
         *
         * @return Response
         */
        public function jsonp($data, $param = 'jsonp', $encode = TRUE) {
            $json = ($encode) ? json_encode($data) : $data;

            $callback = $this->request->query[$param];

            $this->response->clear()
                           ->status(200)
                           ->header('Content-Type', 'application/javascript')
                           ->write($callback . '(' . $json . ');');

            return $this->response;
        }


        /**
         * Returns a 404 Not Found Response
         *
         * @return Response
         */
        public function notFound() {
            $this->response->clear()
                           ->status(404);
//                           ->write('<h1>404 Not Found</h1>' . '<h3>The page you have requested could not be found.</h3>' . str_repeat(' ', 512));
//
//            return $this->response;
            return $this->view->getContent('Error/404');
        }

        /**
         * Returns a Redirect Response.
         *
         * @param string $controller
         * @param string $action
         * @param array  $routeParams
         * @param int    $code
         *
         * @return Response
         */
        public function redirectToAction($controller = "Home", $action = "Index", $routeParams = array(), $code = 302) {
            $base = Wow::get('app.base_url');

            if($base === NULL) {
                $base = $this->request->base;
            }

            $url = "/";
            if($controller != "Home" || $action != "Index" || count($routeParams) > 0) {
                $url .= $controller;
            }
            if($action != "Index" || count($routeParams) > 0) {
                $url .= "/" . $action;
            }
            if(count($routeParams) > 0) {
                $url .= "/" . implode("/", array_values($routeParams));
            }

            // Append base url to redirect url
            if($base != '/' && strpos($url, '://') === FALSE) {
                $url = preg_replace('#/+#', '/', $base . '/' . $url);
            }

            $this->response->clear()
                           ->status($code)
                           ->header('Location', $url)
                           ->write($url);

            return $this->response;
        }


        /**
         * Returns a Redirect Response.
         *
         * @param string $url
         * @param int    $code
         *
         * @return Response
         */
        public function redirectToUrl($url, $code = 302) {
            $base = Wow::get('app.base_url');

            if($base === NULL) {
                $base = $this->request->base;
            }

            // Append base url to redirect url
            if($base != '/' && strpos($url, '://') === FALSE) {
                $url = preg_replace('#/+#', '/', $base . '/' . $url);
            }

            $this->response->clear()
                           ->status($code)
                           ->header('Location', $url)
                           ->write($url);

            return $this->response;
        }


    }