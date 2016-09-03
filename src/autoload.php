<?php

    require_once __DIR__ . '/Wow/Core/Loader.php';
    $objLoader = new Wow\Core\Loader();
    $objLoader->addNamespace('Wow', __DIR__ . '/Wow');
    $objLoader->addNamespace('App', __DIR__ . '/../app');
    $objLoader->registerAutoLoader();