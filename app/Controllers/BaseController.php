<?php

    namespace Wow\Controllers;

    use Wow\Core\Controller;

    class BaseController extends Controller {


        /**
         * Override onStart
         */
        function onActionExecuting() {
            parent::onActionExecuting();
        }

        /**
         * Override onEnd
         */
        function onActionExecuted() {
            parent::onActionExecuted();
        }

    }