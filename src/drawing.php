<?php
declare(strict_types=1);

/**
 * Regalzeichnung: jede Verpackung als Strichfigur. Pro Form: Breite, Höhe,
 * Silhouette (wird schraffiert gefüllt) und Detaillinien.
 */
const SHAPE_GEOMETRY = [
    'can'     => [22, 30, 'M1 3H21V29H1Z', 'M1 6.5H21M1 25.5H21'],
    'tin'     => [30, 12, 'M1 2H29V11H1Z', 'M1 4.5H29M24 2V4.5'],
    'jar'     => [22, 30, 'M3 6H19Q21 8 21 11V27Q21 29 19 29H3Q1 29 1 27V11Q1 8 3 6Z', 'M3 1.5H19V6H3ZM5 14H17V22H5Z'],
    'bottle'  => [14, 40, 'M5 7H9Q13 10 13 15V39H1V15Q1 10 5 7Z', 'M5 1H9V7H5ZM1 21H13M1 31H13'],
    'carton'  => [18, 34, 'M1 9L5 4H13L17 9V33H1Z', 'M5 4V1.5H13V4M1 9H17'],
    'box'     => [26, 32, 'M1 3H25V31H1Z', 'M1 8H25M9 3V8M17 3V8'],
    'bag'     => [24, 30, 'M3 6H21L23 29H1Z', 'M3 6L4 1.5H20L21 6M7 1.5V6M12 1.5V6M17 1.5V6'],
    'sack'    => [30, 30, 'M6 7Q15 10 24 7L28 29H2Z', 'M6 7L9.5 2M24 7L20.5 2M9.5 2Q15 4 20.5 2'],
    'crate'   => [40, 28, 'M1 7H39V27H1Z', 'M14 11H26V14.5H14ZM5 7V3H9V7M13 7V3H17V7M23 7V3H27V7M31 7V3H35V7'],
    'block'   => [30, 18, 'M1 7L29 1V17H1Z', 'M8 12.5H10.5M17 10H19.5M22 14H24.5'],
    'bar'     => [30, 10, 'M1 1H29V9H1Z', 'M8 1V9M15 1V9M22 1V9'],
    'roll'    => [20, 22, 'M1 4H19V21H1Z', 'M7 4Q10 1.5 13 4'],
    'torch'   => [12, 34, 'M1 1H11V10L8 14V33H4V14L1 10Z', 'M1 6H11M4 20H8'],
    'radio'   => [34, 24, 'M1 7H33V23H1Z', 'M24 7L30 1.5M5 11H14V19H5ZM19 12H29M19 15H29M19 18H29'],
    'battery' => [10, 24, 'M1 4H9V23H1Z', 'M3.5 4V1.5H6.5V4M1 9H9'],
    'candle'  => [10, 28, 'M1 10H9V27H1Z', 'M5 8Q2.5 5 5 1.5Q7.5 5 5 8ZM5 8V10'],
    'stove'   => [32, 22, 'M3 9H29V21H3Z', 'M9 9V6H23V9M1 6H31M16 13V17M12 15H20'],
    'kit'     => [32, 24, 'M1 6H31V23H1Z', 'M11 6V2H21V6M16 10.5V18.5M12 14.5H20'],
];

function shape_geo(string $shape): array
{
    return SHAPE_GEOMETRY[$shape] ?? SHAPE_GEOMETRY['box'];
}

/** Gemeinsame SVG-Defs (Formen, Schnittmasken, Schraffuren). Einmal pro Seite ausgeben. */
function drawing_defs(): string
{
    $s = '<svg class="defs" width="0" height="0" aria-hidden="true" focusable="false"><defs>';
    $s .= '<pattern id="hatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path d="M0 0V4" stroke="currentColor" stroke-width="1.6"/></pattern>';
    $s .= '<pattern id="hatch-signal" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path d="M0 0V4" stroke="var(--signal)" stroke-width="1.6"/></pattern>';
    foreach (SHAPE_GEOMETRY as $name => [$w, $h, $sil, $det]) {
        $s .= '<path id="sil-' . $name . '" d="' . $sil . '"/>';
        $s .= '<path id="det-' . $name . '" d="' . $det . '"/>';
        $s .= '<clipPath id="clip-' . $name . '"><use href="#sil-' . $name . '"/></clipPath>';
    }
    return $s . '</defs></svg>';
}

/**
 * Packungen eines Artikels: wie viele gezeichnet werden und wie jede gefüllt ist.
 * Reihenfolge: voll, angebrochen, abgelaufen, fehlend (gestrichelt).
 */
function pack_plan(array $item): array
{
    $pack = $item['pack_size'] && $item['pack_size'] > 0 ? $item['pack_size'] : max($item['target'], $item['stock'], 1);
    $targetPacks = $item['target'] > 0 ? (int) ceil($item['target'] / $pack - 1e-6) : 0;
    $usablePacks = $item['usable'] / $pack;
    $expiredPacks = $item['stock_expired'] / $pack;
    $drawn = max($targetPacks, (int) ceil($usablePacks + $expiredPacks - 1e-6), 1);

    // Große Mengen zusammenfassen, damit ein Regalbrett lesbar bleibt.
    $per = 1;
    while ($drawn / $per > 18) {
        $per++;
    }
    $count = (int) ceil($drawn / $per);
    $u = $usablePacks / $per;
    $x = $expiredPacks / $per;

    $cells = [];
    for ($i = 0; $i < $count; $i++) {
        $fill = max(0, min(1, $u - $i));
        if ($fill > 0.001) {
            $cells[] = ['kind' => 'stock', 'fill' => $fill];
            continue;
        }
        $xFill = max(0, min(1, $u + $x - $i));
        if ($xFill > 0.001) {
            $cells[] = ['kind' => 'expired', 'fill' => $xFill];
            continue;
        }
        $cells[] = ['kind' => $i < (int) ceil($targetPacks / $per - 1e-6) ? 'missing' : 'empty', 'fill' => 0];
    }
    return ['cells' => $cells, 'per' => $per, 'target_packs' => $targetPacks, 'pack' => $pack];
}

/** SVG eines Artikels: Packungen in Stapeln (bis 3 hoch), auf dem Brett stehend. */
function item_figure(array $item, float $scale = 1.0): string
{
    $plan = pack_plan($item);
    $cells = $plan['cells'];
    [$w, $h] = shape_geo($item['shape']);
    $shape = isset(SHAPE_GEOMETRY[$item['shape']]) ? $item['shape'] : 'box';
    $n = count($cells);
    $rows = $h <= 14 ? min(4, max(1, (int) ceil($n / 4))) : ($n > 12 ? 3 : ($n > 5 ? 2 : 1));
    $cols = (int) ceil($n / $rows);
    $gap = 3;
    $vw = $cols * $w + ($cols - 1) * $gap;
    $vh = $rows * $h + ($rows - 1) * 2;
    $doubled = in_array($item['state'], ['soon', 'due'], true);

    $out = '';
    foreach ($cells as $i => $c) {
        $col = intdiv($i, $rows);
        $row = $rows - 1 - ($i % $rows);           // von unten nach oben stapeln
        $x = $col * ($w + $gap);
        $y = $row * ($h + 2);
        $cls = 'pk pk-' . $c['kind'] . ($doubled && $c['kind'] === 'stock' ? ' pk-doubled' : '');
        $out .= '<g class="' . $cls . '" transform="translate(' . $x . ' ' . $y . ')">';
        if ($c['fill'] > 0) {
            $fh = $h * $c['fill'];
            $out .= '<rect class="pk-fill" clip-path="url(#clip-' . $shape . ')" x="0" y="' . round($h - $fh, 2) . '" width="' . $w . '" height="' . round($fh, 2) . '" fill="url(#' . ($c['kind'] === 'expired' ? 'hatch-signal' : 'hatch') . ')"/>';
        }
        $out .= '<use class="pk-sil" href="#sil-' . $shape . '"/>';
        if ($doubled && $c['kind'] === 'stock') {
            $out .= '<use class="pk-dbl" href="#sil-' . $shape . '"/>';
        }
        $out .= '<use class="pk-det" href="#det-' . $shape . '"/>';
        if ($c['kind'] === 'expired') {
            $out .= '<path class="pk-strike" d="M0 ' . $h . 'L' . $w . ' 0"/>';
        }
        $out .= '</g>';
    }
    $pw = round($vw * $scale, 1);
    $ph = round($vh * $scale, 1);
    return '<svg class="fig" viewBox="-1.5 -1.5 ' . ($vw + 3) . ' ' . ($vh + 3) . '" width="' . ($pw + 3) . '" height="' . ($ph + 3) . '" aria-hidden="true" focusable="false">' . $out . '</svg>';
}

/** Kurztext zur Füllung für Bildunterschriften und Screenreader. */
function fill_caption(array $item): string
{
    $plan = pack_plan($item);
    if ($item['pack_size'] && $plan['per'] === 1 && $item['unit'] !== 'stk') {
        $have = $item['usable'] / $plan['pack'];
        return num(floor($have * 10) / 10, 1) . ' / ' . $plan['target_packs'];
    }
    return num($item['usable'], 2) . ' / ' . num($item['target'], 2);
}

function state_text(array $item): string
{
    return match ($item['state']) {
        'expired' => 'abgelaufen',
        'due' => 'fällig',
        'soon' => 'bald fällig',
        default => $item['ratio'] >= 0.999 ? 'komplett' : ($item['usable'] > 0 ? 'teilweise' : 'fehlt'),
    };
}

/** Zeitachse: 30 Tage Rückblick links, dann bis $max Tage nach vorn. Liefert CSS-Variablen in Prozent. */
function timeline_track_vars(int $max, int $warn): string
{
    $span = $max + 30;
    return sprintf('--today: %.3f%%; --warnw: %.3f%%', 30 / $span * 100, min($warn, $max) / $span * 100);
}

function timeline_bar_vars(?int $days, int $max): string
{
    if ($days === null) {
        return '';
    }
    $span = $max + 30;
    $d = max(-30, min($days, $max));
    $start = $d < 0 ? 30 + $d : 30;
    return sprintf('--l: %.3f%%; --w: %.3f%%', $start / $span * 100, abs($d) / $span * 100);
}
