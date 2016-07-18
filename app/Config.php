<?php
    return array(


        //App Variables
        "app"      => array(
            "layout"                => "layout/default",
            "base_url"              => NULL,
            "handle_errors"         => TRUE,
            "log_errors"            => FALSE,
            "router_case_sensitive" => TRUE
        ),


        //Database Variables
        "database" => array(
            //mysql, sqlsrv, pgsql are tested connections and work perfect.
            "driver"   => "mysql",
            "host"     => "127.0.0.1",
            "port"     => "3306",
            "name"     => "databasename",
            "user"     => "username",
            "password" => "password"
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