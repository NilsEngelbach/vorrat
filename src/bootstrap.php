<?php
declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

$configFile = APP_ROOT . '/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('config.php fehlt. Bitte config.sample.php kopieren und anpassen.');
}
$GLOBALS['config'] = require $configFile;

date_default_timezone_set(config('timezone', 'Europe/Berlin'));
mb_internal_encoding('UTF-8');

if (config('debug')) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

require __DIR__ . '/db.php';
require __DIR__ . '/helpers.php';
require __DIR__ . '/install.php';
require __DIR__ . '/model.php';
require __DIR__ . '/auth.php';
require __DIR__ . '/mail.php';
require __DIR__ . '/drawing.php';

function config(string $key, mixed $default = null): mixed
{
    return $GLOBALS['config'][$key] ?? $default;
}

install_if_needed();
