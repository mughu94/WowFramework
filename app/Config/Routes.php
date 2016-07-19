<?php
    return array(
        //Routes
        "DefaultRoute" => array(
            "(/@controller(/@action(/@id)))",
            array(
                "controller" => "Home",
                "action"     => "Index"
            )
        )
    );