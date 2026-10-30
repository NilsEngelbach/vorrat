<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';
require APP_ROOT . '/src/routes.php';

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = base_path();
if ($base !== '' && str_starts_with($path, $base)) {
    $path = substr($path, strlen($base));
}
$path = '/' . trim(rawurldecode($path), '/');
if ($path === '/index.php') {
    $path = '/';
}

dispatch($path);
