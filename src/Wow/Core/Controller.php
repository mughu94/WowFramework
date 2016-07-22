<?php

    namespace Wow\Core;

    use Wow;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;
    use Wow\Template\View;

    class Controller {

        public $route;
        public $request;
        public $response;
        public $view;

        function __construct(Route $route, Request $request) {
            $this->route    = $route;
            $this->request  = $request;
            $this->response = new Response();
            $this->view     = new View($request, $route);
        }

        function __destruct() {
            $this->onEnd();
        }

        /**
         * @return Response Your Decision
         */
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
            if(!method_exists($this, $this->route->params["action"]."Action") || !is_callable(array(
                                                                                                  $this,
                                                                                                  $this->route->params["action"]."Action"
                                                                                              ))
            ) {
                return $this->notFound();
            }
            $routeValues = $this->route->params;
            unset($routeValues["controller"]);
            unset($routeValues["action"]);

            return call_user_func_array(array(
                                            $this,
                                            $this->route->params["action"]."Action"
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
            $viewname = $this->getViewName($viewname);

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
            $viewname = $this->getViewName($viewname);

            return $this->view->getContent($viewname, $data, TRUE);
        }

        /**
         * Finds the right view name.
         *
         * @param string $viewname
         *
         * @return string
         */
        private function getViewName($viewname) {
            if(empty($viewname)) {
                $viewname = $this->route->view;
            }

            return strtolower($viewname);
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

            return $this->view->getContent('error/404');
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
                $url .= implode("-",array_map("strtolower",preg_split('/(?=[A-Z])/',$controller,-1,PREG_SPLIT_NO_EMPTY)));
            }
            if($action != "Index" || count($routeParams) > 0) {
                $url .= "/" . implode("-",array_map("strtolower",preg_split('/(?=[A-Z])/',$action,-1,PREG_SPLIT_NO_EMPTY)));
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