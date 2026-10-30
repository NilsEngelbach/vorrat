<?php
declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $c = config('db');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'] ?? 3306, $c['name']);
        try {
            $pdo = new PDO($dsn, $c['user'], $c['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Keine Verbindung zur Datenbank. Zugangsdaten in config.php prüfen.');
        }
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function all(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function one(string $sql, array $params = []): ?array
{
    $row = q($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function &settings_cache(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (all('SELECT k, v FROM nv_settings') as $r) {
            $cache[$r['k']] = $r['v'];
        }
    }
    return $cache;
}

function setting(string $key, ?string $default = null): ?string
{
    return settings_cache()[$key] ?? $default;
}

function set_setting(string $key, ?string $value): void
{
    q('INSERT INTO nv_settings (k, v) VALUES (?, ?) ON DUPLICATE KEY UPDATE v = VALUES(v)', [$key, $value]);
    $cache = &settings_cache();
    $cache[$key] = $value;
}
