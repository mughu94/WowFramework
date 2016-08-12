<?php

    namespace Wow\Controllers;

    use Wow\Core\Controller;

    class BaseController extends Controller {


        /**
         * Override onActionExecuting
         */
        function onActionExecuting() {
            parent::onActionExecuting();
        }

        /**
         * Override onActionExecuted
         */
        function onActionExecuted() {
            parent::onActionExecuted();
        }

    }