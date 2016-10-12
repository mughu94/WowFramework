<?php

    namespace Wow\Core;

    use Wow;
    use Wow\Net\Request;
    use Wow\Net\Response;
    use Wow\Net\Route;
    use Wow\Template\View;

    class Controller {

        const RESULT_TYPE_VIEW         = "ViewResult";
        const RESULT_TYPE_PARTIALVIEW  = "PartialViewResult";
        const RESULT_TYPE_JSON         = "JsonResult";
        const RESULT_TYPE_JSONP        = "JsonPResult";
        const RESULT_TYPE_HTTPNOTFOUND = "HttpNotFoundResult";
        const RESULT_TYPE_REDIRECT     = "RedirectResult";
        const RESULT_TYPE_FILE         = "FileResult";


        public  $route;
        public  $request;
        public  $response;
        public  $view;
        private $resultType = NULL;

        function __construct(Route $route, Request $request) {
            $this->route    = $route;
            $this->request  = $request;
            $this->response = new Response();
            $this->view     = new View($request, $route);

            //Autodedect User Language. This framework is MVCL ;)
            if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $this->view->setLanguage(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
            }
        }

        function __destruct() {
        }

        /**
         * @return Response Your Decision
         *                  Executes this block before execute the action.
         */
        public function onActionExecuting() {

        }

        /**
         * @return Response Your Decision
         *                  Executes this block after execute the action and before go to view.
         */
        public function onActionExecuted() {

        }

        /**
         * Inits Controller Method by route params
         *
         * @return mixed
         */
        function init($action, $params = array()) {
            if(!method_exists($this, $action) || !is_callable(array(
                                                                  $this,
                                                                  $action
                                                              ))
            ) {
                return $this->notFound();
            }

            $responseExecuting = $this->onActionExecuting();
            if($responseExecuting instanceof Response) {
                return $responseExecuting;
            }

            return call_user_func_array(array(
                                            $this,
                                            $action
                                        ), $params);

        }

        /**
         * Finds the right view name.
         *
         * @param string $viewname Viewname
         *
         * @return string
         */
        private function getViewName($viewname = NULL) {
            if(empty($viewname)) {
                $viewname = "";
                foreach(explode("/", $this->route->params["controller"]) as $piece) {
                    $viewname .= implode("-", array_map("strtolower", preg_split('/(?=[A-Z])/', $piece, -1, PREG_SPLIT_NO_EMPTY))) . "/";
                }
                $viewname .= implode("-", array_map("strtolower", preg_split('/(?=[A-Z])/', $this->route->params["action"], -1, PREG_SPLIT_NO_EMPTY)));
            }

            return strtolower($viewname);
        }

        /**
         * Gets the result type of executed action.
         *
         * @return string
         */
        public function getResultType() {
            return $this->resultType;
        }

        /**
         * Renders View
         *
         * @param string $model    Model
         * @param string $viewname Viewname
         *
         * @return Response
         */
        public function view($model = NULL, $viewname = NULL) {
            $this->resultType = self::RESULT_TYPE_VIEW;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }
            $viewname = $this->getViewName($viewname);

            return $this->view->getResponse($viewname, $model);
        }

        /**
         * Renders Partial View
         *
         * @param mixed  $model    Model
         * @param string $viewname Viewname
         *
         * @return Response
         */
        public function partialView($model = NULL, $viewname = NULL) {
            $this->resultType = self::RESULT_TYPE_PARTIALVIEW;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }
            $viewname = $this->getViewName($viewname);

            return $this->view->getResponse($viewname, $model, TRUE);
        }

        /**
         * Renders data as json object
         *
         * @param array $model  Model
         * @param bool  $encode Encode
         *
         * @return Response
         */
        public function json($model, $encode = TRUE) {
            $this->resultType = self::RESULT_TYPE_JSON;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }
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
         * @param array  $model  Model
         * @param string $param  QueryString Parameter
         * @param bool   $encode Encode
         *
         * @return Response
         */
        public function jsonp($model, $param = 'jsonp', $encode = TRUE) {
            $this->resultType = self::RESULT_TYPE_JSONP;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }
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
            $this->resultType = self::RESULT_TYPE_HTTPNOTFOUND;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }

            return $this->view->getResponse('error/404', NULL);
        }


        function actionResult($controller, $action = "Index", $routeParams = array()) {
            $route = new Route("*", array(
                "controller" => "",
                "action"     => ""
            ), array("*"));

            $route->params               = array_merge($route->params, $routeParams);
            $route->params["controller"] = $controller;
            $route->params["action"]     = $action;

            $actionResponse = Dispatcher::dispatchRoute($route, $this->request);

            return $actionResponse === FALSE ? $this->notFound() : $actionResponse;
        }

        /**
         * Returns a File Response
         *
         * @param string $file
         *
         * @return Response
         */
        public function file($buffer, $mimeType, $fileName, $force_download = FALSE) {
            $this->resultType = self::RESULT_TYPE_FILE;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }
            $this->response->clear()
                           ->status(200)
                           ->header("Content-Type", $mimeType)
                           ->header("Content-Disposition", $force_download ? 'attachment; filename="' . $fileName . '"' : 'filename="' . $fileName . '"')
                           ->write($buffer);
            if($force_download) {
                $this->response->header("", "");
            }

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
            $this->resultType = self::RESULT_TYPE_REDIRECT;
            $responseExecuted = $this->onActionExecuted();
            if($responseExecuted instanceof Response) {
                return $responseExecuted;
            }
            $base = Wow::get('app/base_url');

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