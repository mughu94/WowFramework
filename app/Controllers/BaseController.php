<?php

    namespace App\Controllers;

    use Wow\Core\Controller;
    use Wow\Net\Response;

    class BaseController extends Controller {


        /**
         * Override onActionExecuting
         */
        function onActionExecuting() {
            if(($pass = parent::onActionExecuting()) instanceof Response) {
                return $pass;
            }
        }

        /**
         * Override onActionExecuted
         */
        function onActionExecuted() {
            if(($pass = parent::onActionExecuted()) instanceof Response) {
                return $pass;
            }
        }

        /**
         * Middleware
         *
         * @param string $type
         */
        function middleware($type) {
            switch($type) {

            }
        }

        /**
         * System error page's container. PLS DO NOT REMOVE!!!
         *
         * @param string     $errorCode
         * @param \Exception $errorException
         *
         * @return Response
         */
        function ErrorAction($errorCode, $errorException = NULL) {
            switch($errorCode) {
                case "500":
                    return $this->view(array("error" => $errorException), "error/500");
                    break;
                default:
                    return $this->view(NULL, "error/404");
            }
        }

    }