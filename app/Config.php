<?php
    return array(


        //App Variables
        "app"      => array(
            "layout"                => "Layout/Default",
            "base_url"              => NULL,
            "handle_errors"         => TRUE,
            "log_errors"            => FALSE,
            "router.case_sensitive" => TRUE
        ),


        //Database Variables
        "database" => array(
            //mysql, sqlsrv, pgsql are tested connections and work perfect.
            "connection" => "mysql",
            "host"       => "127.0.0.1",
            "port"       => "3306",
            "name"       => "databasename",
            "user"       => "username",
            "password"   => "password"
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