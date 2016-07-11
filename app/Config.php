<?php
    return array(


        
        //App Variables
        "app"      => array(
            "layout"   => "Layout/Default",
            "base_url" => NULL,
            "handle_errors" => TRUE,
            "log_errors" => FALSE,
            "router.case_sensitive" => TRUE
        ),



        //Routes
        "routes"   => array(
            "DefaultRoute" => array(
                "(/@controller(/@action(/@id)))",
                array(
                    "controller" => "Home",
                    "action"     => "Index"
                )
            )
        )
    );