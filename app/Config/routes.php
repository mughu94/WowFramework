<?php
    return array(
        //Routes
        "DefaultRoute" => array(
            "(/@controller(/@action(/@id)))",
            array(
                "prefix"     => "",
                "controller" => "Home",
                "action"     => "Index"
            )
        )
    );