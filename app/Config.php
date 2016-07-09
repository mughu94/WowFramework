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
            "mainRoute"    => array(
                "(/main(/@action(/@id)))",
                array(
                    "controller" => "Home",
                    "action"     => "Index"
                )
            ),
            "DefaultRoute" => array(
                "(/@controller(/@action(/@id)))",
                array(
                    "controller" => "Home",
                    "action"     => "Index"
                )
            )
        )
    );