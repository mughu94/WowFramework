<?php

    namespace Wow\Core;

    use Wow;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;
    use Wow\Template\View;

    class Controller {

        public  $route;
        public  $request;
        public  $response;
        public  $view;
        private $viewname = NULL;

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
        function init($action, $viewname, $params = array()) {
            if(!method_exists($this, $action) || !is_callable(array(
                                                                  $this,
                                                                  $action
                                                              ))
            ) {
                return $this->notFound();
            }
            $this->viewname = $viewname;

            return call_user_func_array(array(
                                            $this,
                                            $action
                                        ), $params);
        }

        /**
         * Renders View
         *
         * @param string $model Model
         * @param string $viewname Viewname
         *
         * @return Response
         */
        public function view($model = NULL, $viewname = NULL) {
            $viewname = $this->getViewName($viewname);

            return $this->view->getContent($viewname, $model);
        }

        /**
         * Renders Partial View
         *
         * @param mixed  $model Model
         * @param string $viewname Viewname
         *
         * @return Response
         */
        public function partialView($model = NULL, $viewname = NULL) {
            $viewname = $this->getViewName($viewname);

            return $this->view->getContent($viewname, $model, TRUE);
        }

        /**
         * Finds the right view name.
         *
         * @param string $viewname Viewname
         *
         * @return string
         */
        private function getViewName($viewname) {
            if(empty($viewname)) {
                $viewname = $this->viewname;
            }

            return strtolower($viewname);
        }

        /**
         * Renders data as json object
         *
         * @param array $model Model
         * @param bool  $encode Encode
         *
         * @return Response
         */
        public function json($model, $encode = TRUE) {
            $json = ($encode) ? json_encode($model) : $model;

            $this->response->clear()
                           ->status(200)
                           ->header('Content-Type', 'application/json')
                           ->write($json);

            return $this->response;
        }

        /**
         * Renders data as jsonp
         *
         * @param array  $model Model
         * @param string $param QueryString Parameter
         * @param bool   $encode Encode
         *
         * @return Response
         */
        public function jsonp($model, $param = 'jsonp', $encode = TRUE) {
            $json = ($encode) ? json_encode($model) : $model;

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
            return $this->view->getContent('error/404',NULL,TRUE);
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
        public function redirectToAction($controller = NULL, $action = "Index", $routeParams = array(), $code = 302) {
            $base = Wow::get('app.base_url');

            if($base === NULL) {
                $base = $this->request->base;
            }

            if($controller === NULL){
                $controller = $this->route->params["controller"];
            }

            $url = "/";
            if($controller != "Home" || $action != "Index" || count($routeParams) > 0) {
                $url .= implode("-", array_map("strtolower", preg_split('/(?=[A-Z])/', $controller, -1, PREG_SPLIT_NO_EMPTY)));
            }
            if($action != "Index" || count($routeParams) > 0) {
                $url .= "/" . implode("-", array_map("strtolower", preg_split('/(?=[A-Z])/', $action, -1, PREG_SPLIT_NO_EMPTY)));
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