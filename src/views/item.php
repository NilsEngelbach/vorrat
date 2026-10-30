<?php
/** @var array $h @var array $item @var array $batches @var array $log @var array $categories */
$u = $item['unit'];
$per = $u === 'l' ? '100 ml' : '100 g';
$hasNutrition = $u !== 'stk' && ($item['kcal'] + $item['protein'] + $item['fat'] + $item['carbs']) > 0;
$grams = $item['usable'] * 1000;
$personDays = $item['kcal'] > 0 ? ($grams / 100 * $item['kcal']) / KCAL_PER_DAY : 0;
$maxDays = max(365, ...array_map(fn($b) => (int) ($b['days'] ?? 0), $batches ?: [['days' => 0]]));
$maxDays = min($maxDays, 1095);
echo drawing_defs();
?>
<div class="page-head">
  <a class="link-back" href="<?= e(url('vorrat')) ?>"><?= icon('back') ?> Vorrat</a>
  <h1><span class="callout callout-lg"><?= $item['pos'] ?? '–' ?></span> <?= e($item['name']) ?></h1>
  <p class="lede"><?= e($item['cat_name']) ?><?= $item['pack_label'] ? ' · ' . e($item['pack_label']) : '' ?><?= !$item['active'] ? ' · nicht im Plan' : '' ?></p>
</div>

<div class="item-grid">
  <section class="item-figure" aria-label="Füllstand">
    <div class="fig-stage state-<?= e($item['state']) ?>"><?= item_figure($item, 2.2) ?></div>
    <dl class="kv kv-big">
      <div><dt>Ist</dt><dd class="num"><?= e(qty($item['usable'], $u)) ?></dd></div>
      <div><dt>Soll</dt><dd class="num"><?= e(qty($item['target'], $u)) ?></dd></div>
      <div><dt>Fehlt</dt><dd class="num<?= $item['gap'] > 0 ? ' is-gap' : '' ?>"><?= $item['gap'] > 0 ? e(qty($item['gap'], $u)) : '–' ?></dd></div>
    </dl>
    <?php if ($item['stock_expired'] > 0): ?>
      <p class="hint hint-alert"><?= icon('warn') ?> <?= e(qty($item['stock_expired'], $u)) ?> abgelaufen, zählt nicht zum Ist.</p>
    <?php endif ?>
    <div class="item-actions">
      <a class="btn btn-primary" href="<?= e(url('einlagern', ['artikel' => $item['id']])) ?>"><?= icon('plus') ?><span>Einlagern</span></a>
      <?php if ($item['stock'] > 0): ?>
        <form class="inline-form" method="post">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="consume">
          <label class="visually-hidden" for="consume-qty">Menge</label>
          <input class="input input-small num" id="consume-qty" name="qty" inputmode="decimal" value="<?= e(qty_input($item['pack_size'] ?: 1)) ?>">
          <span class="unit"><?= e(UNIT_LABEL[$u]) ?></span>
          <button class="btn"><?= icon('minus') ?><span>Verbraucht</span></button>
        </form>
      <?php endif ?>
    </div>
  </section>

  <section class="item-batches" aria-labelledby="batches-title">
    <div class="section-head"><h2 id="batches-title">Chargen</h2><p class="section-meta">Balkenlänge = Tage bis MHD</p></div>
    <?php if (!$batches): ?>
      <p class="empty">Keine Charge im Vorrat. <a href="<?= e(url('einlagern', ['artikel' => $item['id']])) ?>">Jetzt einlagern</a>, damit sich die Kontur füllt.</p>
    <?php else: ?>
      <ul class="timeline" style="<?= timeline_track_vars($maxDays, $h['warn_days']) ?>">
        <?php foreach ($batches as $b): ?>
          <li class="tl-row state-<?= e($b['state']) ?>" style="<?= timeline_bar_vars($b['days'], $maxDays) ?>">
            <div class="tl-text">
              <span class="tl-qty num"><?= e(qty($b['qty'], $u)) ?></span>
              <span class="tl-mhd num"><?= $b['mhd'] ? e(date_de($b['mhd'])) : 'ohne MHD' ?></span>
              <span class="tl-state"><?= e(STATE_LABEL[$b['state']]) ?><?= $b['mhd'] ? ', ' . e(relative_days($b['days'])) : '' ?></span>
            </div>
            <?php if ($b['mhd']): ?><div class="tl-track" aria-hidden="true"><span class="tl-bar"></span></div><?php endif ?>
            <details class="tl-edit">
              <summary><?= icon('edit') ?><span>Bearbeiten</span></summary>
              <form method="post" action="<?= e(url('charge/' . $b['id'])) ?>" class="form-row">
                <?= csrf_field() ?>
                <input type="hidden" name="back" value="artikel/<?= (int) $item['id'] ?>">
                <label class="field"><span>Menge (<?= e(UNIT_LABEL[$u]) ?>)</span><input class="input num" name="qty" inputmode="decimal" value="<?= e(qty_input($b['qty'])) ?>"></label>
                <label class="field"><span>MHD</span><input class="input" type="date" name="mhd" value="<?= e($b['mhd'] ?? '') ?>"></label>
                <label class="field"><span>Lagerort</span><input class="input" name="location" value="<?= e($b['location'] ?? '') ?>"></label>
                <div class="form-actions">
                  <button class="btn" name="action" value="update"><?= icon('check') ?><span>Speichern</span></button>
                  <button class="btn btn-quiet" name="action" value="consume"><?= icon('minus') ?><span>Ganz verbraucht</span></button>
                  <button class="btn btn-quiet" name="action" value="discard"><?= icon('trash') ?><span>Entsorgt</span></button>
                </div>
              </form>
            </details>
          </li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </section>

  <section class="item-nutrition" aria-labelledby="nut-title">
    <div class="section-head"><h2 id="nut-title">Nährwerte</h2><p class="section-meta">Durchschnittswerte je <?= $per ?></p></div>
    <?php if ($hasNutrition): ?>
      <table class="table nutrition">
        <thead><tr><th scope="col"></th><th class="c-num" scope="col">je <?= $per ?></th><th class="c-num" scope="col">im Vorrat</th></tr></thead>
        <tbody>
          <tr><th scope="row">Energie</th><td class="c-num"><?= num($item['kcal'], 0) ?> kcal</td><td class="c-num"><?= number_format($grams / 100 * $item['kcal'], 0, ',', '.') ?> kcal</td></tr>
          <tr><th scope="row">Eiweiß</th><td class="c-num"><?= num($item['protein'], 1) ?> g</td><td class="c-num"><?= num($grams / 100 * $item['protein'], 0) ?> g</td></tr>
          <tr><th scope="row">Fett</th><td class="c-num"><?= num($item['fat'], 1) ?> g</td><td class="c-num"><?= num($grams / 100 * $item['fat'], 0) ?> g</td></tr>
          <tr><th scope="row">Kohlenhydrate</th><td class="c-num"><?= num($item['carbs'], 1) ?> g</td><td class="c-num"><?= num($grams / 100 * $item['carbs'], 0) ?> g</td></tr>
        </tbody>
      </table>
      <p class="hint">Der Vorrat dieses Artikels deckt rechnerisch <b class="num"><?= num($personDays, 1) ?></b> Personentage Energiebedarf (<?= KCAL_PER_DAY ?> kcal).</p>
    <?php else: ?>
      <p class="empty"><?= $u === 'stk' ? 'Ausrüstung, keine Nährwerte.' : 'Keine Nährwerte hinterlegt. Unten unter „Artikel bearbeiten“ eintragen.' ?></p>
    <?php endif ?>
  </section>

  <section class="item-plan" aria-labelledby="plan-title">
    <div class="section-head"><h2 id="plan-title">Soll</h2><p class="section-meta">BBK-Empfehlung: <?= e(qty($item['recommended'], $u)) ?> (<?= e(num($item['base_qty'], 3)) ?> <?= e(UNIT_LABEL[$u]) ?> <?= e(SCALE_LABEL[$item['scale']]) ?>)</p></div>
    <form method="post" class="form-row">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="target">
      <label class="field"><span>Eigene Sollmenge (<?= e(UNIT_LABEL[$u]) ?>)</span><input class="input num" name="target" inputmode="decimal" value="<?= e(qty_input($item['target'], 2)) ?>"></label>
      <div class="form-actions">
        <button class="btn"><?= icon('check') ?><span>Speichern</span></button>
        <?php if ($item['target_override'] !== null): ?><button class="btn btn-quiet" name="reset" value="1">Auf Empfehlung zurücksetzen</button><?php endif ?>
      </div>
    </form>
  </section>
</div>

<details class="disclosure">
  <summary><?= icon('edit') ?><span>Artikel bearbeiten</span></summary>
  <?php partial('item-form', ['item' => $item, 'categories' => $categories, 'action' => 'edit']) ?>
  <?php if ($item['is_custom']): ?>
    <form method="post" class="danger-zone" data-confirm="<?= e($item['name']) ?> mit allen Chargen löschen?">
      <?= csrf_field() ?>
      <button class="btn btn-quiet" name="action" value="delete"><?= icon('trash') ?><span>Artikel löschen</span></button>
    </form>
  <?php endif ?>
</details>

<?php if ($log): ?>
  <section class="item-log" aria-labelledby="log-title">
    <div class="section-head"><h2 id="log-title">Bewegungen</h2></div>
    <ul class="log-list log-wide">
      <?php foreach ($log as $l): ?>
        <li><span class="log-sign log-<?= e($l['type']) ?>"><?= $l['type'] === 'in' ? '+' : '−' ?></span><span class="log-name"><?= ['in' => 'Eingelagert', 'out' => 'Verbraucht', 'discard' => 'Entsorgt'][$l['type']] ?><?= $l['mhd'] ? ', MHD ' . e(date_de($l['mhd'])) : '' ?></span><span class="num"><?= e(qty((float) $l['qty'], $u)) ?></span><span class="log-when num"><?= e(date('d.m.Y', strtotime($l['created_at']))) ?></span></li>
      <?php endforeach ?>
    </ul>
  </section>
<?php endif ?>
