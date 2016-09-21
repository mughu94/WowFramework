<?php
    return array(


        //App Variables
        "app"      => array(
            "theme"                 => "default", //Default theme for site
            "layout"                => "layout/default", //Default Layout for pages
            "language"              => "en", //Default language
            "base_url"              => NULL,
            "handle_errors"         => TRUE,
            "log_errors"            => FALSE,
            "router_case_sensitive" => TRUE
        ),


        //Database Variables
        "database" => array(
            "DefaultConnection" => array(
                //mysql, sqlsrv, pgsql are tested connections and work perfect.
                "driver"   => "mysql",
                "host"     => "127.0.0.1",
                "port"     => "3306",
                "name"     => "databasename",
                "user"     => "username",
                "password" => "password"
            )
        )
    );