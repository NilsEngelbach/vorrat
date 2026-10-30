<?php
/** @var array $h @var array $groups @var int $count @var array $replace */
?>
<div class="page-head">
  <h1>Einkaufsliste</h1>
  <p class="lede">Was bis zum Soll fehlt, in Packungen gerundet. Abhaken merkt sich dieses Gerät; „Einlagern“ trägt die Menge gleich ein.</p>
  <div class="page-tools">
    <button type="button" class="btn" data-print><?= icon('print') ?><span>Drucken</span></button>
    <button type="button" class="btn btn-quiet" data-clear-checks>Haken zurücksetzen</button>
  </div>
</div>

<?php if ($count === 0 && !$replace): ?>
  <p class="empty">Nichts zu kaufen. Jede Position hat ihr Soll erreicht.</p>
<?php endif ?>

<?php if ($replace): ?>
  <section class="shop-group" aria-labelledby="sg-replace">
    <div class="section-head"><h2 id="sg-replace"><?= icon('warn') ?>Ersatz für fällige Chargen</h2></div>
    <ul class="shop-list">
      <?php foreach ($replace as $b): ?>
        <li class="shop-item state-<?= e($b['state']) ?>">
          <label class="check"><input type="checkbox" data-check="r<?= (int) $b['id'] ?>"><span class="shop-name"><?= e($b['name']) ?></span></label>
          <span class="shop-qty num"><?= e(qty($b['qty'], $b['unit'])) ?></span>
          <span class="shop-pack">MHD <?= e(date_de($b['mhd'])) ?>, <?= e(STATE_LABEL[$b['state']]) ?></span>
          <a class="btn btn-small" href="<?= e(url('einlagern', ['artikel' => $b['item_id'], 'menge' => qty_input($b['qty'])])) ?>"><?= icon('plus') ?><span>Einlagern</span></a>
        </li>
      <?php endforeach ?>
    </ul>
  </section>
<?php endif ?>

<?php foreach ($groups as $g): ?>
  <section class="shop-group" aria-labelledby="sg-<?= e($g['slug']) ?>">
    <div class="section-head"><h2 id="sg-<?= e($g['slug']) ?>"><?= e($g['name']) ?></h2></div>
    <ul class="shop-list">
      <?php foreach ($g['items'] as $it): $p = packs($it['gap'], $it['pack_size']); $buy = $p && $it['pack_size'] ? $p * $it['pack_size'] : $it['gap']; ?>
        <li class="shop-item">
          <label class="check"><input type="checkbox" data-check="i<?= (int) $it['id'] ?>"><span class="shop-name"><?= e($it['name']) ?></span></label>
          <span class="shop-qty num"><?= e(qty($it['gap'], $it['unit'])) ?></span>
          <span class="shop-pack num"><?= $p ? $p . ' × ' . e($it['pack_label'] ?: qty($it['pack_size'], $it['unit'])) : '' ?></span>
          <a class="btn btn-small" href="<?= e(url('einlagern', ['artikel' => $it['id'], 'menge' => qty_input($buy)])) ?>"><?= icon('plus') ?><span>Einlagern</span></a>
        </li>
      <?php endforeach ?>
    </ul>
  </section>
<?php endforeach ?>
