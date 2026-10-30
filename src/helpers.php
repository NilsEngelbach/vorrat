<?php
declare(strict_types=1);

function e(mixed $v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Basis-Pfad der App (für Installationen in Unterordnern). */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
        $base = rtrim($dir, '/');
    }
    return $base;
}

/**
 * Absolute Adresse der App ohne Schrägstrich am Ende, für Cron-Link und Links in E-Mails.
 * Im Browser aus der aktuellen Anfrage; auf der Kommandozeile die zuletzt im Browser
 * gesehene Adresse, sonst base_url aus der config.php.
 */
function app_url(): string
{
    if (PHP_SAPI === 'cli') {
        return rtrim((string) (setting('app_url') ?? config('base_url', '')), '/');
    }
    $https = (($_SERVER['HTTPS'] ?? '') !== '' && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
        || (string) ($_SERVER['SERVER_PORT'] ?? '') === '443';
    return ($https ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . base_path();
}

function url(string $path = '', array $query = []): string
{
    $u = base_path() . '/' . ltrim($path, '/');
    if ($query) {
        $u .= '?' . http_build_query($query);
    }
    return $u;
}

function asset(string $path): string
{
    $file = APP_ROOT . '/public/assets/' . $path;
    $v = is_file($file) ? filemtime($file) : 0;
    return url('assets/' . $path) . '?v=' . $v;
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query));
    exit;
}

function flash(?string $msg = null, string $type = 'ok'): ?array
{
    if ($msg !== null) {
        $_SESSION['flash'] = ['msg' => $msg, 'type' => $type];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('Sitzung abgelaufen. Bitte Seite neu laden und erneut versuchen.');
    }
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function post(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

/** Deutsche Dezimalzahl („1,5“ oder „1.5“) einlesen. */
function parse_num(mixed $v): ?float
{
    if ($v === null) {
        return null;
    }
    $v = trim(str_replace([' ', "\u{202F}"], '', (string) $v));
    if ($v === '') {
        return null;
    }
    if (str_contains($v, ',')) {
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
    }
    return is_numeric($v) ? (float) $v : null;
}

function num(float $n, int $maxDecimals = 2): string
{
    $s = number_format($n, $maxDecimals, ',', '.');
    if ($maxDecimals > 0) {
        $s = rtrim(rtrim($s, '0'), ',');
    }
    return $s === '-0' ? '0' : $s;
}

const UNIT_LABEL = ['kg' => 'kg', 'l' => 'l', 'stk' => 'Stk.'];

/** Menge mit Einheit, kleine Mengen in g/ml. */
function qty(float $n, string $unit): string
{
    if ($unit === 'stk') {
        return num($n, 1) . "\u{202F}Stk.";
    }
    if (abs($n) > 0 && abs($n) < 1) {
        return num($n * 1000, 0) . "\u{202F}" . ($unit === 'kg' ? 'g' : 'ml');
    }
    return num($n, 2) . "\u{202F}" . UNIT_LABEL[$unit];
}

/** Menge im Eingabefeld (immer Basiseinheit, deutsches Komma). */
function qty_input(?float $n, int $decimals = 3): string
{
    return $n === null ? '' : num($n, $decimals);
}

const MONTHS_DE = ['Jan.', 'Feb.', 'März', 'Apr.', 'Mai', 'Juni', 'Juli', 'Aug.', 'Sept.', 'Okt.', 'Nov.', 'Dez.'];

function date_de(?string $ymd, bool $long = false): string
{
    if (!$ymd) {
        return '–';
    }
    $t = strtotime($ymd);
    if ($long) {
        return date('j', $t) . '. ' . MONTHS_DE[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
    }
    return date('d.m.Y', $t);
}

function today(): string
{
    return date('Y-m-d');
}

function days_until(?string $ymd): ?int
{
    if (!$ymd) {
        return null;
    }
    $a = new DateTimeImmutable(today());
    $b = new DateTimeImmutable($ymd);
    return (int) $a->diff($b)->format('%r%a');
}

function relative_days(?int $d): string
{
    if ($d === null) {
        return 'ohne MHD';
    }
    return match (true) {
        $d < -1 => 'seit ' . abs($d) . ' Tagen abgelaufen',
        $d === -1 => 'seit gestern abgelaufen',
        $d === 0 => 'heute',
        $d === 1 => 'morgen',
        $d < 60 => 'in ' . $d . ' Tagen',
        $d < 730 => 'in ' . (int) round($d / 30.4) . ' Monaten',
        default => 'in ' . num($d / 365, 1) . ' Jahren',
    };
}

function render(string $view, array $data = [], ?string $layout = 'layout'): void
{
    extract($data, EXTR_SKIP);
    ob_start();
    require APP_ROOT . '/src/views/' . $view . '.php';
    $content = ob_get_clean();
    if ($layout === null) {
        echo $content;
        return;
    }
    require APP_ROOT . '/src/views/' . $layout . '.php';
}

function partial(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_ROOT . '/src/views/' . $view . '.php';
}

/** Eigene Strich-Icons, alle 24er Raster, 2px Linie. */
function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'plus'   => '<path d="M12 5v14M5 12h14"/>',
        'minus'  => '<path d="M5 12h14"/>',
        'check'  => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'arrow'  => '<path d="M5 12h13M13 7l5 5-5 5"/>',
        'back'   => '<path d="M19 12H6M11 7l-5 5 5 5"/>',
        'warn'   => '<path d="M12 4l9 16H3z"/><path d="M12 10v4.5M12 17v.5"/>',
        'clock'  => '<circle cx="12" cy="12" r="8"/><path d="M12 7.5V12l3 2"/>',
        'cross'  => '<path d="M6.5 6.5l11 11M17.5 6.5l-11 11"/>',
        'print'  => '<path d="M7 9V4h10v5M7 17H4v-7h16v7h-3"/><path d="M7 14h10v6H7z"/>',
        'mail'   => '<path d="M3.5 6h17v12h-17z"/><path d="M4 6.5l8 6.5 8-6.5"/>',
        'out'    => '<path d="M10 5H5v14h5M14 8l4 4-4 4M18 12H9"/>',
        'trash'  => '<path d="M5 7h14M10 7V4.5h4V7M7 7l1 12.5h8L17 7"/>',
        'edit'   => '<path d="M5 19l1-4L16 5l3 3L9 18z"/><path d="M14 7l3 3"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6"/><path d="M15 15l5 5"/>',
        'box'    => '<path d="M4 8h16v12H4zM4 8l2-4h12l2 4M10 12h4"/>',
        'cart'   => '<path d="M3 4h3l2 11h11l2-8H7"/><circle cx="9.5" cy="19" r="1.2"/><circle cx="17" cy="19" r="1.2"/>',
        'plan'   => '<path d="M5 3.5h14v17H5z"/><path d="M8.5 8h7M8.5 12h7M8.5 16h4"/>',
    ];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . ($paths[$name] ?? '') . '</svg>';
}
