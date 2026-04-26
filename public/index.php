<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

// Bootstrap config before handling request
$app->instance('config', new \Illuminate\Config\Repository([
    'app' => require __DIR__.'/../config/app.php',
    'cors' => require __DIR__.'/../config/cors.php',
    'database' => require __DIR__.'/../config/database.php',
    'logging' => require __DIR__.'/../config/logging.php',
    'mail' => require __DIR__.'/../config/mail.php',
    'queue' => require __DIR__.'/../config/queue.php',
    'services' => require __DIR__.'/../config/services.php',
    'session' => require __DIR__.'/../config/session.php',
    'view' => ['compiled' => $app->bootstrapPath('cache/_ide_helper_meta.php')],
]));

// Register essential service providers
$app->register(\Illuminate\Events\EventServiceProvider::class);
$app->register(\Illuminate\Routing\RoutingServiceProvider::class);
$app->register(\Illuminate\View\ViewServiceProvider::class);
$app->register(\Illuminate\Foundation\Providers\FoundationServiceProvider::class);
$app->register(\Illuminate\Database\DatabaseServiceProvider::class);
$app->register(\Illuminate\Session\SessionServiceProvider::class);

$app->handleRequest(Request::capture());
