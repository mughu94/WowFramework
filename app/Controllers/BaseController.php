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
        function middleware($type){
            switch($type){

            }
        }

    }