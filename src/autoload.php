<?php

require_once __DIR__ . '/Wow/Core/Loader.php';
$objLoader = new Wow\Core\Loader();
$objLoader->addNamespace('Wow', __DIR__ . '/Wow');
$objLoader->addNamespace('Wow\\Controllers', __DIR__ . '/../app/Controllers/');
$objLoader->addNamespace('Wow\\Models', __DIR__ . '/../app/Models/');
$objLoader->registerAutoLoader();