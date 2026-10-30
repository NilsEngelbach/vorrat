<?php
$attn = attention_count();
$nav = $nav ?? '';
$f = flash();
$links = [
    'overview' => ['', 'Übersicht'],
    'stock' => ['vorrat', 'Vorrat'],
    'mhd' => ['haltbarkeit', 'Haltbarkeit'],
    'plan' => ['planung', 'Planung'],
    'shopping' => ['einkaufsliste', 'Einkaufsliste'],
];
?><!doctype html>
<html lang="de">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#fcfcfa">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title ?? 'Notvorrat') ?> · Notvorrat</title>
<link rel="icon" href="<?= e(asset('mark.svg')) ?>" type="image/svg+xml">
<link rel="preload" href="<?= e(url('assets/fonts/barlow-latin-400-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= e(url('assets/fonts/barlow-semi-condensed-latin-600-normal.woff2')) ?>" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
<script src="<?= e(asset('app.js')) ?>" defer></script>
</head>
<body class="page-<?= e($nav ?: 'plain') ?>">
<a class="skip" href="#main">Zum Inhalt</a>
<div class="sheet">
  <header class="masthead">
    <a class="brand" href="<?= e(url('')) ?>" aria-label="Notvorrat, Übersicht">
      <svg class="brand-mark" viewBox="0 0 32 32" aria-hidden="true"><path d="M3 2V30M29 2V30M3 11H29M3 20H29M3 29H29"/><path d="M7 11V6H12V11M15 11V7.5H20V11M8 20V15H11V20M14 20V14H17.5V20M21 29V24H26V29M7 29V25H14V29"/></svg>
      <span>Notvorrat</span>
    </a>
    <nav class="nav" aria-label="Hauptnavigation">
      <?php foreach ($links as $key => [$href, $label]): ?>
        <a href="<?= e(url($href)) ?>"<?= $nav === $key ? ' aria-current="page"' : '' ?>>
          <?= e($label) ?><?php if ($key === 'mhd' && $attn): ?><span class="badge" title="<?= $attn ?> Chargen erreichen bald ihr MHD"><?= $attn ?></span><?php endif ?>
        </a>
      <?php endforeach ?>
    </nav>
    <div class="masthead-end">
      <a class="settings-link<?= $nav === 'settings' ? ' is-current' : '' ?>" href="<?= e(url('einstellungen')) ?>"<?= $nav === 'settings' ? ' aria-current="page"' : '' ?>>Einstellungen</a>
      <a class="btn btn-primary btn-stockup" href="<?= e(url('einlagern')) ?>"><?= icon('plus') ?><span>Einlagern</span></a>
    </div>
  </header>

  <?php if ($f): ?>
    <div class="flash flash-<?= e($f['type']) ?>" role="status"><?= icon($f['type'] === 'error' ? 'warn' : 'check') ?><span><?= e($f['msg']) ?></span></div>
  <?php endif ?>

  <main id="main" class="main">
    <?= $content ?>
  </main>

  <footer class="colophon">
    <span>Mengen nach BBK-Ratgeber für Notfallvorsorge, skaliert auf <?= e(household_label(household())) ?>.</span>
    <span>Nährwerte sind Durchschnittswerte, keine Herstellerangaben.</span>
  </footer>
</div>

<nav class="tabbar" aria-label="Schnellnavigation">
  <a href="<?= e(url('')) ?>"<?= $nav === 'overview' ? ' aria-current="page"' : '' ?>><?= icon('box') ?><span>Übersicht</span></a>
  <a href="<?= e(url('vorrat')) ?>"<?= $nav === 'stock' ? ' aria-current="page"' : '' ?>><?= icon('search') ?><span>Vorrat</span></a>
  <a href="<?= e(url('haltbarkeit')) ?>"<?= $nav === 'mhd' ? ' aria-current="page"' : '' ?>><?= icon('clock') ?><span>MHD<?php if ($attn): ?> <b class="badge"><?= $attn ?></b><?php endif ?></span></a>
  <a href="<?= e(url('planung')) ?>"<?= $nav === 'plan' ? ' aria-current="page"' : '' ?>><?= icon('plan') ?><span>Plan</span></a>
  <a href="<?= e(url('einkaufsliste')) ?>"<?= $nav === 'shopping' ? ' aria-current="page"' : '' ?>><?= icon('cart') ?><span>Einkauf</span></a>
</nav>
</body>
</html>
