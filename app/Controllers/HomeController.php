<?php

    namespace Wow\Controllers;

    use Wow;

    class HomeController extends BaseController {


        public function Index() {


            $this->view->set('title', 'Homepage');
            $this->view->set('keywords', 'app, wow app, php');
            $this->view->set('description', 'Wellcome to wow framework.');

            return $this->view();

        }


    }

