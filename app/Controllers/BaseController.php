<?php

    namespace App\Controllers;

    use Wow\Core\Controller;
    use Wow\Net\Response;

    class BaseController extends Controller {


        /**
         * Override onActionExecuting
         */
        function onActionExecuting() {
            $actionResponse = parent::onActionExecuting();
            if($actionResponse instanceof Response) {
                return $actionResponse;
            }
        }

        /**
         * Override onActionExecuted
         */
        function onActionExecuted() {
            $actionResponse = parent::onActionExecuted();
            if($actionResponse instanceof Response) {
                return $actionResponse;
            }
        }

    }