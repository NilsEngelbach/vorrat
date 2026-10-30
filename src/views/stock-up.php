<?php
/** @var array $items @var array $groups @var array $values @var ?string $error @var array $today_log @var array $locations */
$meta = [];
foreach ($items as $it) {
    $meta[$it['id']] = ['unit' => UNIT_LABEL[$it['unit']], 'pack' => $it['pack_size'], 'packLabel' => $it['pack_label'], 'months' => (int) $it['shelf_months'], 'name' => $it['name']];
}
$selected = $values['item_id'] ? ($meta[$values['item_id']] ?? null) : null;
echo drawing_defs();
?>
<div class="page-head">
  <h1>Einlagern</h1>
  <p class="lede">Artikel wählen, Menge und MHD eintragen. „Weiter erfassen“ hält das Formular für den nächsten Artikel offen.</p>
</div>

<?php if ($error): ?><p class="flash flash-error" role="alert"><?= icon('warn') ?><span><?= e($error) ?></span></p><?php endif ?>

<form method="post" class="stockup" data-meta='<?= e(json_encode($meta, JSON_UNESCAPED_UNICODE)) ?>'>
  <?= csrf_field() ?>
  <fieldset class="pick">
    <legend>Artikel</legend>
    <label class="filter">
      <?= icon('search') ?>
      <span class="visually-hidden">Artikel suchen</span>
      <input type="search" placeholder="Suchen, z. B. Nudeln" data-filter=".pick-item" autocomplete="off" <?= $values['item_id'] ? '' : 'autofocus' ?>>
    </label>
    <div class="pick-groups">
      <?php foreach ($groups as $g): ?>
        <div class="pick-group">
          <p class="pick-group-name"><?= e($g['name']) ?></p>
          <div class="pick-grid">
            <?php foreach ($g['items'] as $it): ?>
              <label class="pick-item" data-name="<?= e(mb_strtolower($it['name'] . ' ' . $g['name'])) ?>">
                <input type="radio" name="item_id" value="<?= (int) $it['id'] ?>"<?= (int) $values['item_id'] === (int) $it['id'] ? ' checked' : '' ?> required>
                <svg class="pick-shape" viewBox="-1.5 -1.5 <?= shape_geo($it['shape'])[0] + 3 ?> <?= shape_geo($it['shape'])[1] + 3 ?>" aria-hidden="true"><use class="pk-sil" href="#sil-<?= e($it['shape']) ?>"/><use class="pk-det" href="#det-<?= e($it['shape']) ?>"/></svg>
                <span class="pick-name"><?= e($it['name']) ?></span>
                <span class="pick-gap num"><?= $it['gap'] > 0 ? 'fehlt ' . e(qty($it['gap'], $it['unit'])) : 'komplett' ?></span>
              </label>
            <?php endforeach ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
    <p class="filter-empty" hidden>Kein Artikel gefunden. Eigene Artikel legst du unter <a href="<?= e(url('planung')) ?>#neu">Planung</a> an.</p>
  </fieldset>

  <fieldset class="entry">
    <legend>Charge</legend>
    <p class="entry-item" data-selected><?= $selected ? e($selected['name']) : 'Noch kein Artikel gewählt' ?></p>
    <div class="entry-qty">
      <div class="field" data-packs-field<?= $selected && $selected['pack'] ? '' : ' hidden' ?>>
        <span id="packs-label">Packungen <span class="pack-label" data-pack-label><?= $selected ? e($selected['packLabel'] ?? '') : '' ?></span></span>
        <div class="stepper" role="group" aria-labelledby="packs-label">
          <button type="button" class="btn btn-icon" data-step="-1" aria-label="Eine Packung weniger"><?= icon('minus') ?></button>
          <input class="input num" name="packs" inputmode="numeric" value="1" aria-labelledby="packs-label" data-packs>
          <button type="button" class="btn btn-icon" data-step="1" aria-label="Eine Packung mehr"><?= icon('plus') ?></button>
        </div>
      </div>
      <label class="field">
        <span>Menge in <b data-unit><?= $selected ? e($selected['unit']) : 'kg' ?></b></span>
        <input class="input input-lg num" name="qty" inputmode="decimal" value="<?= e($values['qty']) ?>" data-qty placeholder="0">
      </label>
    </div>
    <div class="field">
      <label for="mhd">Mindesthaltbarkeitsdatum</label>
      <input class="input input-lg" type="date" id="mhd" name="mhd" value="<?= e($values['mhd']) ?>" min="<?= e(date('Y-m-d', strtotime('-1 year'))) ?>" data-mhd>
      <div class="chips" data-mhd-chips>
        <button type="button" class="chip" data-months="3">+3 Mon.</button>
        <button type="button" class="chip" data-months="6">+6 Mon.</button>
        <button type="button" class="chip" data-months="12">+1 Jahr</button>
        <button type="button" class="chip" data-months="24">+2 Jahre</button>
        <button type="button" class="chip chip-typical" data-typical hidden>typisch</button>
        <button type="button" class="chip" data-months="0">ohne MHD</button>
      </div>
    </div>
    <label class="field">
      <span>Lagerort <span class="optional">optional</span></span>
      <input class="input" name="location" list="locations" maxlength="60" value="<?= e($values['location']) ?>" placeholder="z. B. Keller, Regal links">
      <datalist id="locations"><?php foreach ($locations as $l): ?><option value="<?= e($l) ?>"><?php endforeach ?></datalist>
    </label>
    <div class="form-actions">
      <button class="btn btn-primary btn-lg" name="again" value="1"><?= icon('plus') ?><span>Einlagern &amp; weiter erfassen</span></button>
      <button class="btn btn-lg"><?= icon('check') ?><span>Einlagern</span></button>
    </div>
  </fieldset>
</form>

<?php if ($today_log): ?>
  <section class="today" aria-labelledby="today-title">
    <div class="section-head"><h2 id="today-title">Heute eingelagert</h2><a class="link-arrow" href="<?= e(url('')) ?>">Zum Regal <?= icon('arrow') ?></a></div>
    <ul class="log-list log-wide">
      <?php foreach ($today_log as $l): ?>
        <li><span class="log-sign log-in">+</span><span class="log-name"><a href="<?= e(url('artikel/' . $l['item_id'])) ?>"><?= e($l['name']) ?></a></span><span class="num"><?= e(qty((float) $l['qty'], $l['unit'])) ?></span><span class="log-when num"><?= $l['mhd'] ? 'MHD ' . e(date_de($l['mhd'])) : 'ohne MHD' ?></span></li>
      <?php endforeach ?>
    </ul>
  </section>
<?php endif ?>
