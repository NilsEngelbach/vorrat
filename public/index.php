<?php
declare(strict_types=1);

// PHP-Entwicklungsserver (php -S … public/index.php): vorhandene Dateien liefert er selbst aus,
// und SCRIPT_NAME wäre sonst die angefragte Adresse statt dieser Datei.
if (PHP_SAPI === 'cli-server') {
    if (is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH))) {
        return false;
    }
    $_SERVER['SCRIPT_NAME'] = '/index.php';
}

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
