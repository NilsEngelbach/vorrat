<?php
declare(strict_types=1);

const KCAL_PER_DAY = 2200;          // BBK-Bezugswert je erwachsener Person
const WATER_L_PER_DAY = 2.0;        // Trinken und Kochen je Person
const REF_PROTEIN = 55.0;           // g/Tag, EU-Referenzmenge auf 2200 kcal skaliert
const REF_FAT = 77.0;
const REF_CARBS = 286.0;

const STATE_LABEL = [
    'fresh' => 'frisch',
    'soon' => 'bald fällig',
    'due' => 'fällig',
    'expired' => 'abgelaufen',
    'none' => 'ohne MHD',
];

const SCALE_LABEL = [
    'person' => 'je Person · 10 Tage',
    'head' => 'je Person',
    'pet' => 'je Tier · 10 Tage',
    'fixed' => 'je Haushalt',
];

const SHAPES = [
    'can' => 'Dose', 'tin' => 'Flache Dose', 'jar' => 'Glas', 'bottle' => 'Flasche', 'carton' => 'Tetrapak',
    'box' => 'Schachtel', 'bag' => 'Beutel', 'sack' => 'Sack / Netz', 'crate' => 'Kiste', 'block' => 'Block',
    'bar' => 'Tafel', 'roll' => 'Rolle', 'torch' => 'Taschenlampe', 'radio' => 'Radio', 'battery' => 'Batterie',
    'candle' => 'Kerze', 'stove' => 'Kocher', 'kit' => 'Koffer',
];

function household(): array
{
    $adults = (int) setting('adults', '2');
    $children = (int) setting('children', '0');
    $factor = (float) setting('child_factor', '0.75');
    return [
        'adults' => $adults,
        'children' => $children,
        'pets' => (int) setting('pets', '0'),
        'days' => max(1, (int) setting('target_days', '10')),
        'child_factor' => $factor,
        'persons' => $adults + $children * $factor,   // Verzehr-Äquivalent
        'heads' => $adults + $children,
        'name' => (string) setting('household_name', ''),
        'warn_days' => max(1, (int) setting('warn_days', '30')),
        'due_days' => max(0, (int) setting('due_days', '7')),
    ];
}

function household_label(array $h): string
{
    $parts = [$h['adults'] . ' ' . ($h['adults'] === 1 ? 'Erwachsener' : 'Erwachsene')];
    if ($h['children']) {
        $parts[] = $h['children'] . ' ' . ($h['children'] === 1 ? 'Kind' : 'Kinder');
    }
    if ($h['pets']) {
        $parts[] = $h['pets'] . ' ' . ($h['pets'] === 1 ? 'Tier' : 'Tiere');
    }
    return implode(', ', $parts);
}

/** Empfohlene Sollmenge nach BBK für diesen Haushalt. */
function recommended(array $item, array $h): float
{
    $base = (float) $item['base_qty'];
    $q = match ($item['scale']) {
        'person' => $base * $h['persons'] * $h['days'] / 10,
        'head' => $base * $h['heads'],
        'pet' => $base * $h['pets'] * $h['days'] / 10,
        default => $base,
    };
    if ($item['unit'] === 'stk') {
        $q = ceil($q - 1e-9);
    }
    return round($q, $item['unit'] === 'stk' ? 0 : 2);
}

function target(array $item, array $h): float
{
    return $item['target_override'] !== null ? (float) $item['target_override'] : recommended($item, $h);
}

function batch_state(?string $mhd, array $h): string
{
    if (!$mhd) {
        return 'none';
    }
    $d = days_until($mhd);
    return match (true) {
        $d < 0 => 'expired',
        $d <= $h['due_days'] => 'due',
        $d <= $h['warn_days'] => 'soon',
        default => 'fresh',
    };
}

/**
 * Alle Artikel mit Bestand, Soll und Zustand. Positionsnummern laufen durch die ganze Liste,
 * damit Zeichnung und Stückliste dieselbe Nummer zeigen.
 */
function load_items(bool $onlyActive = true): array
{
    $h = household();
    $t = today();
    $due = date('Y-m-d', strtotime("+{$h['due_days']} days"));
    $warn = date('Y-m-d', strtotime("+{$h['warn_days']} days"));
    $rows = all(
        "SELECT i.*, c.slug AS cat_slug, c.name AS cat_name, c.guideline AS cat_guideline, c.sort AS cat_sort,
                COALESCE(SUM(b.qty), 0) AS stock,
                COALESCE(SUM(CASE WHEN b.mhd < :t1 THEN b.qty ELSE 0 END), 0) AS stock_expired,
                MIN(CASE WHEN b.mhd IS NOT NULL THEN b.mhd END) AS next_mhd,
                SUM(CASE WHEN b.mhd < :t2 THEN 1 ELSE 0 END) AS n_expired,
                SUM(CASE WHEN b.mhd >= :t3 AND b.mhd <= :due THEN 1 ELSE 0 END) AS n_due,
                SUM(CASE WHEN b.mhd > :due2 AND b.mhd <= :warn THEN 1 ELSE 0 END) AS n_soon,
                COUNT(b.id) AS n_batches
         FROM nv_items i
         JOIN nv_categories c ON c.id = i.category_id
         LEFT JOIN nv_batches b ON b.item_id = i.id AND b.qty > 0
         " . ($onlyActive ? 'WHERE i.active = 1' : '') . "
         GROUP BY i.id
         ORDER BY c.sort, i.sort, i.name",
        ['t1' => $t, 't2' => $t, 't3' => $t, 'due' => $due, 'due2' => $due, 'warn' => $warn]
    );
    $pos = 0;
    foreach ($rows as &$r) {
        foreach (['stock', 'stock_expired', 'base_qty', 'kcal', 'protein', 'fat', 'carbs'] as $k) {
            $r[$k] = (float) $r[$k];
        }
        $r['target_override'] = $r['target_override'] !== null ? (float) $r['target_override'] : null;
        $r['pack_size'] = $r['pack_size'] !== null ? (float) $r['pack_size'] : null;
        $r['recommended'] = recommended($r, $h);
        $r['target'] = target($r, $h);
        $r['usable'] = max(0, $r['stock'] - $r['stock_expired']);
        $r['gap'] = max(0, $r['target'] - $r['usable']);
        $r['ratio'] = $r['target'] > 0 ? $r['usable'] / $r['target'] : ($r['usable'] > 0 ? 1 : 0);
        $r['state'] = match (true) {
            $r['n_expired'] > 0 => 'expired',
            $r['n_due'] > 0 => 'due',
            $r['n_soon'] > 0 => 'soon',
            default => 'fresh',
        };
        $r['relevant'] = $r['target'] > 0 || $r['stock'] > 0;
        $r['pos'] = $r['relevant'] ? ++$pos : null;
    }
    return $rows;
}

function group_by_category(array $items): array
{
    $out = [];
    foreach ($items as $it) {
        $s = $it['cat_slug'];
        $out[$s] ??= ['slug' => $s, 'name' => $it['cat_name'], 'guideline' => $it['cat_guideline'], 'items' => []];
        $out[$s]['items'][] = $it;
    }
    return $out;
}

/**
 * Versorgungsdauer in Tagen aus Kalorien, Wasser und Makronährstoffen.
 * $qty 'usable' rechnet mit dem Vorrat (Abgelaufenes zählt nicht), 'target' mit den Sollmengen des Plans.
 */
function coverage(array $items, array $h, string $qty = 'usable'): array
{
    $kcal = $p = $f = $c = $water = 0.0;
    $kcalTarget = 0.0;
    foreach ($items as $it) {
        if (!coverage_kind($it)) {
            continue;
        }
        $grams = $it[$qty] * 1000;              // kg → g, l → ml (Dichte ≈ 1)
        $kcal += $grams / 100 * $it['kcal'];
        $p += $grams / 100 * $it['protein'];
        $f += $grams / 100 * $it['fat'];
        $c += $grams / 100 * $it['carbs'];
        $kcalTarget += $it['target'] * 1000 / 100 * $it['kcal'];
        if (coverage_kind($it) === 'drink') {
            $water += $it[$qty];
        }
    }
    $persons = max(0.01, $h['persons']);
    $heads = max(1, $h['heads']);
    $food = $kcal / (KCAL_PER_DAY * $persons);
    $drink = $water / (WATER_L_PER_DAY * $heads);
    return [
        'kcal' => $kcal,
        'kcal_target' => $kcalTarget,
        'food_days' => $food,
        'water_days' => $drink,
        'days' => min($food, $drink),
        'protein_days' => $p / (REF_PROTEIN * $persons),
        'fat_days' => $f / (REF_FAT * $persons),
        'carbs_days' => $c / (REF_CARBS * $persons),
        'water_l' => $water,
    ];
}

/** Wie ein Artikel in die Versorgungsdauer eingeht: 'drink', 'food' oder gar nicht (Stückgut, Tierfutter). */
function coverage_kind(array $item): ?string
{
    if ($item['unit'] === 'stk' || $item['cat_slug'] === 'haustiere') {
        return null;
    }
    return $item['cat_slug'] === 'getraenke' ? 'drink' : 'food';
}

function attention_batches(int $limit = 0): array
{
    $h = household();
    $warn = date('Y-m-d', strtotime("+{$h['warn_days']} days"));
    $sql = "SELECT b.*, i.name, i.unit, i.shape, c.slug AS cat_slug
            FROM nv_batches b JOIN nv_items i ON i.id = b.item_id JOIN nv_categories c ON c.id = i.category_id
            WHERE b.qty > 0 AND b.mhd IS NOT NULL AND b.mhd <= ?
            ORDER BY b.mhd, i.name" . ($limit ? ' LIMIT ' . (int) $limit : '');
    $rows = all($sql, [$warn]);
    foreach ($rows as &$r) {
        $r['qty'] = (float) $r['qty'];
        $r['state'] = batch_state($r['mhd'], $h);
        $r['days'] = days_until($r['mhd']);
    }
    return $rows;
}

function attention_count(): int
{
    $h = household();
    $warn = date('Y-m-d', strtotime("+{$h['warn_days']} days"));
    return (int) (one('SELECT COUNT(*) n FROM nv_batches WHERE qty > 0 AND mhd IS NOT NULL AND mhd <= ?', [$warn])['n'] ?? 0);
}

function find_item(int $id): ?array
{
    foreach (load_items(false) as $it) {
        if ((int) $it['id'] === $id) {
            return $it;
        }
    }
    return null;
}

function item_batches(int $itemId): array
{
    $h = household();
    $rows = all('SELECT * FROM nv_batches WHERE item_id = ? AND qty > 0 ORDER BY mhd IS NULL, mhd, id', [$itemId]);
    foreach ($rows as &$r) {
        $r['qty'] = (float) $r['qty'];
        $r['qty_initial'] = (float) $r['qty_initial'];
        $r['state'] = batch_state($r['mhd'], $h);
        $r['days'] = days_until($r['mhd']);
    }
    return $rows;
}

function add_batch(int $itemId, float $qty, ?string $mhd, ?string $location): void
{
    $now = date('Y-m-d H:i:s');
    q('INSERT INTO nv_batches (item_id, qty, qty_initial, mhd, location, added_at) VALUES (?,?,?,?,?,?)',
        [$itemId, $qty, $qty, $mhd ?: null, $location ?: null, $now]);
    q("INSERT INTO nv_log (item_id, type, qty, mhd, created_at) VALUES (?, 'in', ?, ?, ?)", [$itemId, $qty, $mhd ?: null, $now]);
}

/** Entnahme nach dem Prinzip „first expired, first out“. Gibt die tatsächlich entnommene Menge zurück. */
function consume(int $itemId, float $qty): float
{
    $left = $qty;
    $batches = all('SELECT id, qty, mhd FROM nv_batches WHERE item_id = ? AND qty > 0 ORDER BY mhd IS NULL, mhd, id', [$itemId]);
    $now = date('Y-m-d H:i:s');
    foreach ($batches as $b) {
        if ($left <= 1e-9) {
            break;
        }
        $take = min((float) $b['qty'], $left);
        q('UPDATE nv_batches SET qty = qty - ? WHERE id = ?', [$take, $b['id']]);
        q("INSERT INTO nv_log (item_id, type, qty, mhd, created_at) VALUES (?, 'out', ?, ?, ?)", [$itemId, $take, $b['mhd'], $now]);
        $left -= $take;
    }
    q('DELETE FROM nv_batches WHERE qty <= 0.0005');
    return $qty - max(0, $left);
}

function batch_take(int $batchId, float $qty, string $type): void
{
    $b = one('SELECT * FROM nv_batches WHERE id = ?', [$batchId]);
    if (!$b) {
        return;
    }
    $take = min((float) $b['qty'], $qty);
    q('UPDATE nv_batches SET qty = qty - ? WHERE id = ?', [$take, $batchId]);
    q('INSERT INTO nv_log (item_id, type, qty, mhd, created_at) VALUES (?, ?, ?, ?, ?)',
        [$b['item_id'], $type === 'discard' ? 'discard' : 'out', $take, $b['mhd'], date('Y-m-d H:i:s')]);
    q('DELETE FROM nv_batches WHERE qty <= 0.0005');
}

function recent_log(int $limit = 8): array
{
    return all('SELECT l.*, i.name, i.unit FROM nv_log l JOIN nv_items i ON i.id = l.item_id ORDER BY l.id DESC LIMIT ' . (int) $limit);
}

function packs(float $amount, ?float $packSize): ?int
{
    if (!$packSize || $packSize <= 0 || $amount <= 0) {
        return null;
    }
    return (int) ceil($amount / $packSize - 1e-9);
}
