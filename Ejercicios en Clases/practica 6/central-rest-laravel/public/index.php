<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Cargar vendor local si existe, o reutilizar el vendor de Laravel del workspace
$autoload = file_exists(__DIR__.'/../vendor/autoload.php')
    ? __DIR__.'/../vendor/autoload.php'
    : __DIR__.'/../../../sistemafinal/vendor/autoload.php';

$loader = require $autoload;
$loader->setPsr4('App\\', __DIR__.'/../app/');
$loader->setPsr4('Database\\Factories\\', __DIR__.'/../database/factories/');
$loader->setPsr4('Database\\Seeders\\', __DIR__.'/../database/seeders/');

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
