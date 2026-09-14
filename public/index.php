<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto create .env on server if missing
if (!file_exists(__DIR__ . '/../.env')) {
    $envDefault = "APP_NAME=Laravel\n"
        . "APP_ENV=production\n"
        . "APP_KEY=base64:OjnzH4nHGuLH+19zxTFUH/tWDJwSTJVG7r3eL1dwXRs=\n"
        . "APP_DEBUG=true\n"
        . "APP_URL=https://presensiarjuna.kinikutau.com\n\n"
        . "LOG_CHANNEL=stack\n\n"
        . "DB_CONNECTION=mysql\n"
        . "DB_HOST=127.0.0.1\n"
        . "DB_PORT=3306\n"
        . "DB_DATABASE=u916236352_presensiarjuna\n"
        . "DB_USERNAME=u916236352_ngadmin\n"
        . "DB_PASSWORD=Brunbrun2010!\n\n"
        . "BROADCAST_DRIVER=log\n"
        . "CACHE_DRIVER=file\n"
        . "QUEUE_CONNECTION=sync\n"
        . "SESSION_DRIVER=file\n"
        . "SESSION_LIFETIME=120\n";
    @file_put_contents(__DIR__ . '/../.env', $envDefault);
}

// Ensure writeable storage directories exist
@mkdir(__DIR__ . '/../storage/framework/sessions', 0777, true);
@mkdir(__DIR__ . '/../storage/framework/views', 0777, true);
@mkdir(__DIR__ . '/../storage/framework/cache/data', 0777, true);
@mkdir(__DIR__ . '/../storage/logs', 0777, true);
@mkdir(__DIR__ . '/../bootstrap/cache', 0777, true);

if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
