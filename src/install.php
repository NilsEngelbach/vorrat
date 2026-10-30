<?php
declare(strict_types=1);

/** Legt Tabellen an und befüllt die BBK-Empfehlung, falls die Datenbank leer ist. */
function install_if_needed(): void
{
    $exists = one("SHOW TABLES LIKE 'nv_settings'");
    if ($exists) {
        return;
    }
    $sql = file_get_contents(__DIR__ . '/schema.sql');
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        db()->exec($stmt);
    }
    seed_catalog();

    $defaults = [
        'adults' => '2', 'children' => '0', 'pets' => '0', 'target_days' => '10',
        'child_factor' => '0.75', 'warn_days' => '30', 'due_days' => '7',
        'household_name' => '', 'notify_email' => '', 'cron_token' => bin2hex(random_bytes(16)),
    ];
    foreach ($defaults as $k => $v) {
        set_setting($k, $v);
    }
}

function seed_catalog(): void
{
    $cat = require __DIR__ . '/catalog.php';
    $ids = [];
    foreach ($cat['categories'] as [$slug, $name, $guide, $sort]) {
        q('INSERT INTO nv_categories (slug, name, guideline, sort) VALUES (?, ?, ?, ?)', [$slug, $name, $guide, $sort]);
        $ids[$slug] = (int) db()->lastInsertId();
    }
    $sort = 0;
    foreach ($cat['items'] as $it) {
        [$c, $name, $unit, $base, $scale, $pack, $label, $kcal, $p, $f, $kh, $months] = $it;
        $sort += 10;
        q('INSERT INTO nv_items (category_id, name, unit, base_qty, scale, pack_size, pack_label, kcal, protein, fat, carbs, shelf_months, shape, active, sort)
           VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $ids[$c], $name, $unit, $base, $scale, $pack, $label, $kcal, $p, $f, $kh, $months,
            $cat['shapes'][$name] ?? 'box', 1, $sort,
        ]);
    }
}
