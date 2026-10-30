<?php
declare(strict_types=1);

const SESSION_DAYS = 30;
const MAX_FAILS = 5;
const LOCK_SECONDS = 300;

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('notvorrat');
    ini_set('session.gc_maxlifetime', (string) (SESSION_DAYS * 86400));
    session_set_cookie_params([
        'lifetime' => SESSION_DAYS * 86400,
        'path' => base_path() . '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function is_set_up(): bool
{
    return setting('password_hash') !== null && setting('password_hash') !== '';
}

function is_logged_in(): bool
{
    // Passwortwechsel meldet alle anderen Sitzungen ab.
    return !empty($_SESSION['auth']) && ($_SESSION['auth'] === auth_fingerprint());
}

function auth_fingerprint(): string
{
    return substr(hash('sha256', (string) setting('password_hash')), 0, 24);
}

function login_locked_for(): int
{
    $fails = (int) setting('login_fails', '0');
    $at = (int) setting('login_fail_at', '0');
    if ($fails >= MAX_FAILS && time() - $at < LOCK_SECONDS) {
        return LOCK_SECONDS - (time() - $at);
    }
    return 0;
}

function attempt_login(string $password): bool
{
    if (login_locked_for() > 0) {
        return false;
    }
    if (password_verify($password, (string) setting('password_hash'))) {
        session_regenerate_id(true);
        $_SESSION['auth'] = auth_fingerprint();
        set_setting('login_fails', '0');
        return true;
    }
    $fails = (int) setting('login_fails', '0');
    $at = (int) setting('login_fail_at', '0');
    if (time() - $at >= LOCK_SECONDS) {
        $fails = 0;
    }
    set_setting('login_fails', (string) ($fails + 1));
    set_setting('login_fail_at', (string) time());
    usleep(400000);
    return false;
}

function set_password(string $password): void
{
    set_setting('password_hash', password_hash($password, PASSWORD_DEFAULT));
    session_regenerate_id(true);
    $_SESSION['auth'] = auth_fingerprint();
}

function logout(): void
{
    $_SESSION = [];
    session_destroy();
}
