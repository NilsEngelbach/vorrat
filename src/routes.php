<?php
declare(strict_types=1);

function dispatch(string $path): void
{
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: DENY');

    if ($path === '/cron/mhd') {
        cron_mhd();
        return;
    }

    start_session();

    if (!is_set_up()) {
        $path === '/setup' ? page_setup() : redirect('setup');
        return;
    }
    if ($path === '/login') {
        page_login();
        return;
    }
    if (!is_logged_in()) {
        redirect('login', $path !== '/' ? ['next' => $path] : []);
    }
    // Für den Cronjob auf der Kommandozeile merken, unter welcher Adresse die App läuft.
    if (setting('app_url') !== app_url()) {
        set_setting('app_url', app_url());
    }
    if (is_post()) {
        csrf_check();
    }

    if (preg_match('#^/artikel/(\d+)$#', $path, $m)) {
        page_item((int) $m[1]);
        return;
    }
    if (preg_match('#^/charge/(\d+)$#', $path, $m) && is_post()) {
        action_batch((int) $m[1]);
        return;
    }

    match ($path) {
        '/' => page_overview(),
        '/vorrat' => page_stock(),
        '/einlagern' => page_stock_up(),
        '/haltbarkeit' => page_mhd(),
        '/planung' => page_plan(),
        '/einkaufsliste' => page_shopping(),
        '/einstellungen' => page_settings(),
        '/logout' => (function () {
            if (is_post()) {
                logout();
            }
            redirect('login');
        })(),
        default => not_found(),
    };
}

function not_found(): void
{
    http_response_code(404);
    render('not-found', ['title' => 'Nicht gefunden', 'nav' => '']);
}

/* ---------- Zugang ---------- */

function page_setup(): void
{
    $error = null;
    if (is_post()) {
        csrf_check();
        $pw = (string) post('password');
        $adults = max(1, (int) post('adults', 2));
        if (mb_strlen($pw) < 8) {
            $error = 'Das Passwort braucht mindestens 8 Zeichen.';
        } elseif ($pw !== (string) post('password2')) {
            $error = 'Die beiden Passwörter stimmen nicht überein.';
        } else {
            set_setting('household_name', trim((string) post('household_name')));
            set_setting('adults', (string) $adults);
            set_setting('children', (string) max(0, (int) post('children', 0)));
            set_setting('pets', (string) max(0, (int) post('pets', 0)));
            set_setting('target_days', (string) max(1, min(90, (int) post('target_days', 10))));
            set_password($pw);
            flash('Eingerichtet. Der Regal-Plan ist nach BBK-Empfehlung für euren Haushalt berechnet.');
            redirect('');
        }
    }
    render('setup', ['title' => 'Einrichten', 'error' => $error], 'layout-plain');
}

function page_login(): void
{
    if (is_logged_in()) {
        redirect('');
    }
    $error = null;
    if (is_post()) {
        csrf_check();
        if (attempt_login((string) post('password'))) {
            $next = (string) ($_GET['next'] ?? '');
            redirect(preg_match('#^/[a-z0-9/\-]*$#', $next) ? ltrim($next, '/') : '');
        }
        $locked = login_locked_for();
        $error = $locked > 0
            ? 'Zu viele Versuche. Bitte in ' . (int) ceil($locked / 60) . ' Minuten erneut versuchen.'
            : 'Das Passwort stimmt nicht.';
    }
    render('login', ['title' => 'Anmelden', 'error' => $error], 'layout-plain');
}

/* ---------- Seiten ---------- */

function page_overview(): void
{
    $h = household();
    $items = load_items();
    render('overview', [
        'title' => 'Übersicht',
        'nav' => 'overview',
        'h' => $h,
        'items' => $items,
        'groups' => group_by_category(array_filter($items, fn($i) => $i['relevant'])),
        'cov' => coverage($items, $h),
        'plan' => coverage($items, $h, 'target'),
        'attention' => attention_batches(6),
        'log' => recent_log(5),
    ]);
}

function page_stock(): void
{
    $h = household();
    $items = array_filter(load_items(), fn($i) => $i['relevant']);
    $batches = [];
    foreach (all('SELECT * FROM nv_batches WHERE qty > 0 ORDER BY mhd IS NULL, mhd, id') as $b) {
        $b['qty'] = (float) $b['qty'];
        $b['state'] = batch_state($b['mhd'], $h);
        $b['days'] = days_until($b['mhd']);
        $batches[$b['item_id']][] = $b;
    }
    render('stock', [
        'title' => 'Vorrat',
        'nav' => 'stock',
        'h' => $h,
        'groups' => group_by_category($items),
        'batches' => $batches,
    ]);
}

function page_item(int $id): void
{
    $item = find_item($id);
    if (!$item) {
        not_found();
        return;
    }
    $h = household();
    if (is_post()) {
        $action = (string) post('action');
        if ($action === 'consume') {
            $q = parse_num(post('qty'));
            if ($q && $q > 0) {
                $done = consume($id, $q);
                flash(qty($done, $item['unit']) . ' ' . $item['name'] . ' verbraucht (älteste Charge zuerst).');
            }
        } elseif ($action === 'target') {
            $t = parse_num(post('target'));
            q('UPDATE nv_items SET target_override = ? WHERE id = ?', [post('reset') ? null : $t, $id]);
            flash('Sollmenge gespeichert.');
        } elseif ($action === 'edit') {
            save_item_fields($id, false);
            flash('Artikel gespeichert.');
        } elseif ($action === 'delete' && $item['is_custom']) {
            q('DELETE FROM nv_items WHERE id = ?', [$id]);
            flash('Artikel gelöscht.');
            redirect('planung');
        }
        redirect('artikel/' . $id);
    }
    render('item', [
        'title' => $item['name'],
        'nav' => 'stock',
        'h' => $h,
        'item' => $item,
        'batches' => item_batches($id),
        'log' => all('SELECT * FROM nv_log WHERE item_id = ? ORDER BY id DESC LIMIT 10', [$id]),
        'categories' => all('SELECT * FROM nv_categories ORDER BY sort'),
    ]);
}

function action_batch(int $id): void
{
    $b = one('SELECT * FROM nv_batches WHERE id = ?', [$id]);
    if (!$b) {
        redirect('vorrat');
    }
    $item = one('SELECT * FROM nv_items WHERE id = ?', [$b['item_id']]);
    $action = (string) post('action');
    $amount = parse_num(post('qty')) ?? (float) $b['qty'];
    if ($action === 'consume' || $action === 'discard') {
        batch_take($id, $amount, $action);
        flash(qty(min($amount, (float) $b['qty']), $item['unit']) . ' ' . $item['name'] . ($action === 'discard' ? ' entsorgt.' : ' verbraucht.'));
    } elseif ($action === 'update') {
        $mhd = (string) post('mhd');
        q('UPDATE nv_batches SET mhd = ?, qty = ?, location = ?, notified_stage = 0 WHERE id = ?', [
            preg_match('/^\d{4}-\d{2}-\d{2}$/', $mhd) ? $mhd : null,
            max(0, parse_num(post('qty')) ?? (float) $b['qty']),
            trim((string) post('location')) ?: null,
            $id,
        ]);
        q('DELETE FROM nv_batches WHERE qty <= 0.0005');
        flash('Charge aktualisiert.');
    }
    $back = (string) post('back');
    redirect(preg_match('#^[a-z0-9/\-]*$#', $back) ? $back : 'vorrat');
}

function page_stock_up(): void
{
    $items = array_values(load_items());
    $error = null;
    $values = ['item_id' => (int) ($_GET['artikel'] ?? 0), 'qty' => (string) ($_GET['menge'] ?? ''), 'mhd' => '', 'location' => ''];
    if (is_post()) {
        $values = ['item_id' => (int) post('item_id'), 'qty' => (string) post('qty'), 'mhd' => (string) post('mhd'), 'location' => trim((string) post('location'))];
        $packs = parse_num(post('packs'));
        $q = parse_num($values['qty']);
        $item = find_item($values['item_id']);
        if ($item && $packs && !$q && $item['pack_size']) {
            $q = $packs * $item['pack_size'];
        }
        if (!$item) {
            $error = 'Bitte einen Artikel auswählen.';
        } elseif (!$q || $q <= 0) {
            $error = 'Bitte eine Menge größer als 0 angeben.';
        } elseif ($values['mhd'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['mhd'])) {
            $error = 'Das MHD bitte als Datum angeben.';
        } else {
            add_batch($item['id'], $q, $values['mhd'] ?: null, $values['location']);
            set_setting('last_location', $values['location']);
            flash(qty($q, $item['unit']) . ' ' . $item['name'] . ' eingelagert' . ($values['mhd'] ? ', MHD ' . date_de($values['mhd']) : '') . '.');
            post('again') ? redirect('einlagern') : redirect('', ['neu' => $item['id']]);
        }
    }
    if (!is_post()) {
        $values['location'] = (string) setting('last_location', '');
    }
    render('stock-up', [
        'title' => 'Einlagern',
        'nav' => 'stockup',
        'items' => $items,
        'groups' => group_by_category($items),
        'values' => $values,
        'error' => $error,
        'today_log' => all("SELECT l.*, i.name, i.unit FROM nv_log l JOIN nv_items i ON i.id = l.item_id WHERE l.type = 'in' AND l.created_at >= ? ORDER BY l.id DESC", [today() . ' 00:00:00']),
        'locations' => array_column(all('SELECT DISTINCT location FROM nv_batches WHERE location IS NOT NULL ORDER BY location'), 'location'),
    ]);
}

function page_mhd(): void
{
    $h = household();
    $rows = all('SELECT b.*, i.name, i.unit, i.shape, c.slug AS cat_slug FROM nv_batches b JOIN nv_items i ON i.id = b.item_id JOIN nv_categories c ON c.id = i.category_id WHERE b.qty > 0 ORDER BY b.mhd IS NULL, b.mhd, i.name');
    $groups = ['expired' => [], 'due' => [], 'soon' => [], 'fresh' => [], 'none' => []];
    foreach ($rows as $r) {
        $r['qty'] = (float) $r['qty'];
        $r['state'] = batch_state($r['mhd'], $h);
        $r['days'] = days_until($r['mhd']);
        $groups[$r['state']][] = $r;
    }
    $wasted = one("SELECT COUNT(*) n FROM nv_log WHERE type = 'discard' AND created_at >= ?", [date('Y-m-d', strtotime('-365 days'))]);
    render('mhd', [
        'title' => 'Haltbarkeit',
        'nav' => 'mhd',
        'h' => $h,
        'groups' => $groups,
        'total' => count($rows),
        'wasted' => (int) ($wasted['n'] ?? 0),
    ]);
}

function save_item_fields(?int $id, bool $custom): int
{
    $unit = in_array(post('unit'), ['kg', 'l', 'stk'], true) ? post('unit') : 'kg';
    $scale = in_array(post('scale'), ['person', 'head', 'pet', 'fixed'], true) ? post('scale') : 'person';
    $shape = array_key_exists((string) post('shape'), SHAPES) ? post('shape') : 'box';
    $fields = [
        'category_id' => (int) post('category_id'),
        'name' => mb_substr(trim((string) post('name')), 0, 120),
        'unit' => $unit,
        'base_qty' => parse_num(post('base_qty')) ?? 0,
        'scale' => $scale,
        'pack_size' => parse_num(post('pack_size')),
        'pack_label' => mb_substr(trim((string) post('pack_label')), 0, 60) ?: null,
        'kcal' => parse_num(post('kcal')) ?? 0,
        'protein' => parse_num(post('protein')) ?? 0,
        'fat' => parse_num(post('fat')) ?? 0,
        'carbs' => parse_num(post('carbs')) ?? 0,
        'shelf_months' => (int) post('shelf_months', 0),
        'shape' => $shape,
    ];
    if ($id) {
        $set = implode(', ', array_map(fn($k) => "$k = ?", array_keys($fields)));
        q("UPDATE nv_items SET $set WHERE id = ?", [...array_values($fields), $id]);
        return $id;
    }
    $fields['is_custom'] = $custom ? 1 : 0;
    $fields['sort'] = 9999;
    $cols = implode(', ', array_keys($fields));
    $ph = implode(', ', array_fill(0, count($fields), '?'));
    q("INSERT INTO nv_items ($cols) VALUES ($ph)", array_values($fields));
    return (int) db()->lastInsertId();
}

function page_plan(): void
{
    if (is_post()) {
        $action = (string) post('action');
        if ($action === 'household') {
            set_setting('household_name', mb_substr(trim((string) post('household_name')), 0, 60));
            set_setting('adults', (string) max(1, min(20, (int) post('adults'))));
            set_setting('children', (string) max(0, min(20, (int) post('children'))));
            set_setting('pets', (string) max(0, min(20, (int) post('pets'))));
            set_setting('target_days', (string) max(1, min(90, (int) post('target_days'))));
            $f = parse_num(post('child_factor'));
            set_setting('child_factor', (string) ($f !== null ? max(0.1, min(1, $f)) : 0.75));
            flash('Haushalt gespeichert. Alle Sollmengen sind neu berechnet.');
        } elseif ($action === 'targets') {
            $active = array_map('intval', (array) post('active', []));
            foreach ((array) post('target', []) as $id => $val) {
                $t = parse_num($val);
                $item = one('SELECT * FROM nv_items WHERE id = ?', [(int) $id]);
                if (!$item) {
                    continue;
                }
                $rec = recommended($item + ['target_override' => null], household());
                $override = ($t === null || abs($t - $rec) < 0.0051) ? null : $t;
                q('UPDATE nv_items SET target_override = ?, active = ? WHERE id = ?', [$override, in_array((int) $id, $active, true) ? 1 : 0, (int) $id]);
            }
            flash('Planung gespeichert.');
        } elseif ($action === 'reset') {
            q('UPDATE nv_items SET target_override = NULL, active = 1 WHERE is_custom = 0');
            flash('Alle Sollmengen auf die BBK-Empfehlung zurückgesetzt.');
        } elseif ($action === 'add') {
            if (trim((string) post('name')) === '') {
                flash('Bitte einen Namen für den Artikel angeben.', 'error');
            } else {
                $id = save_item_fields(null, true);
                flash('Artikel angelegt.');
                redirect('artikel/' . $id);
            }
        }
        redirect('planung');
    }
    $h = household();
    $items = load_items(false);
    render('plan', [
        'title' => 'Planung',
        'nav' => 'plan',
        'h' => $h,
        'groups' => group_by_category($items),
        'plan' => coverage(array_filter($items, fn($i) => $i['active']), $h, 'target'),
        'categories' => all('SELECT * FROM nv_categories ORDER BY sort'),
    ]);
}

function page_shopping(): void
{
    $h = household();
    $items = array_filter(load_items(), fn($i) => $i['gap'] > 0.0005);
    $replace = attention_batches();
    render('shopping', [
        'title' => 'Einkaufsliste',
        'nav' => 'shopping',
        'h' => $h,
        'groups' => group_by_category($items),
        'count' => count($items),
        'replace' => array_filter($replace, fn($b) => in_array($b['state'], ['due', 'expired'], true)),
    ]);
}

function page_settings(): void
{
    $error = null;
    if (is_post()) {
        $action = (string) post('action');
        if ($action === 'notify') {
            $mail = trim((string) post('notify_email'));
            if ($mail !== '' && !filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Die E-Mail-Adresse sieht nicht gültig aus.';
            } else {
                set_setting('notify_email', $mail);
                set_setting('warn_days', (string) max(1, min(365, (int) post('warn_days'))));
                set_setting('due_days', (string) max(0, min(60, (int) post('due_days'))));
                flash('Benachrichtigung gespeichert.');
                redirect('einstellungen');
            }
        } elseif ($action === 'test') {
            $r = run_mhd_digest(true);
            flash($r['sent'] ? 'Sammelmail an ' . setting('notify_email') . ' gesendet.' : $r['reason'], $r['sent'] ? 'ok' : 'error');
            redirect('einstellungen');
        } elseif ($action === 'testmail') {
            $mail = trim((string) post('notify_email'));
            if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $error = 'Für die Test-Mail bitte eine gültige E-Mail-Adresse eintragen.';
            } else {
                $err = send_test_mail($mail);
                flash($err === null
                    ? "Test-Mail an {$mail} übergeben. Kommt sie nicht an: Spam-Ordner prüfen und ob der Absender " . config('mail_from', '') . ' beim Hoster existiert.'
                    : 'Test-Mail fehlgeschlagen: ' . $err, $err === null ? 'ok' : 'error');
                redirect('einstellungen');
            }
        } elseif ($action === 'password') {
            if (!password_verify((string) post('current'), (string) setting('password_hash'))) {
                $error = 'Das aktuelle Passwort stimmt nicht.';
            } elseif (mb_strlen((string) post('new')) < 8) {
                $error = 'Das neue Passwort braucht mindestens 8 Zeichen.';
            } elseif (post('new') !== post('new2')) {
                $error = 'Die beiden neuen Passwörter stimmen nicht überein.';
            } else {
                set_password((string) post('new'));
                flash('Passwort geändert. Andere Geräte müssen sich neu anmelden.');
                redirect('einstellungen');
            }
        } elseif ($action === 'token') {
            set_setting('cron_token', bin2hex(random_bytes(16)));
            flash('Neuer Cron-Link erzeugt.');
            redirect('einstellungen');
        }
    }
    render('settings', [
        'title' => 'Einstellungen',
        'nav' => 'settings',
        'h' => household(),
        'error' => $error,
        'cron_url' => app_url() . '/cron/mhd?' . http_build_query(['token' => setting('cron_token')]),
    ]);
}

function cron_mhd(): void
{
    header('Content-Type: text/plain; charset=utf-8');
    $token = (string) ($_GET['token'] ?? '');
    if ($token === '' || !hash_equals((string) setting('cron_token'), $token)) {
        http_response_code(403);
        exit('Ungültiger Token.');
    }
    $r = run_mhd_digest();
    echo $r['reason'] . " (neu: {$r['new']}, gesamt: {$r['total']})\n";
}
