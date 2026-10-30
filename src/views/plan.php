<?php
/** @var array $h @var array $groups @var array $plan @var array $categories */
$kcalPerDay = $plan['kcal_target'] / max(1, $h['days']) / max(0.01, $h['persons']);
$ref = [
    'kcal' => KCAL_PER_DAY, 'water' => WATER_L_PER_DAY, 'protein' => REF_PROTEIN, 'fat' => REF_FAT, 'carbs' => REF_CARBS,
    'persons' => max(0.01, $h['persons']), 'heads' => max(1, $h['heads']),
];
$cover = [
    'food_days' => 'Energie', 'water_days' => 'Wasser', 'protein_days' => 'Eiweiß', 'fat_days' => 'Fett', 'carbs_days' => 'Kohlenhydrate',
];
?>
<div class="page-head">
  <h1>Planung</h1>
  <p class="lede">Die Empfehlung folgt dem BBK-Ratgeber und rechnet sich aus Haushalt und Zieldauer. Jede Sollmenge lässt sich überschreiben, Artikel lassen sich abwählen oder ergänzen.</p>
</div>

<form method="post" class="household" aria-labelledby="hh-title">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="household">
  <h2 id="hh-title">Haushalt</h2>
  <div class="hh-grid">
    <?php foreach ([['adults', 'Erwachsene', 1], ['children', 'Kinder', 0], ['pets', 'Haustiere', 0], ['target_days', 'Zieldauer in Tagen', 1]] as [$k, $label, $min]): ?>
      <div class="field">
        <span id="hh-<?= $k ?>"><?= $label ?></span>
        <div class="stepper" role="group" aria-labelledby="hh-<?= $k ?>">
          <button type="button" class="btn btn-icon" data-step="-1" aria-label="<?= $label ?> verringern"><?= icon('minus') ?></button>
          <input class="input num" name="<?= $k ?>" inputmode="numeric" value="<?= (int) ($k === 'target_days' ? $h['days'] : $h[$k]) ?>" data-min="<?= $min ?>" aria-labelledby="hh-<?= $k ?>">
          <button type="button" class="btn btn-icon" data-step="1" aria-label="<?= $label ?> erhöhen"><?= icon('plus') ?></button>
        </div>
      </div>
    <?php endforeach ?>
    <label class="field"><span>Kind zählt als … Personen</span><input class="input num" name="child_factor" inputmode="decimal" value="<?= e(num($h['child_factor'], 2)) ?>"></label>
    <label class="field"><span>Name <span class="optional">optional</span></span><input class="input" name="household_name" maxlength="60" value="<?= e($h['name']) ?>" placeholder="z. B. Familie Muster"></label>
  </div>
  <div class="form-actions">
    <button class="btn btn-primary"><?= icon('check') ?><span>Haushalt speichern und neu berechnen</span></button>
    <p class="hint num">Plan ergibt <?= number_format($kcalPerDay, 0, ',', '.') ?> kcal je Person und Tag (Bezug <?= KCAL_PER_DAY ?> kcal) und <?= num(WATER_L_PER_DAY, 0) ?> l Getränke je Person und Tag.</p>
  </div>
</form>

<form method="post" class="plan-form" data-ref="<?= e(json_encode($ref)) ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="targets">
  <section class="plan-cover" aria-labelledby="pc-title">
    <div class="section-head">
      <h2 id="pc-title">Reichweite des Plans</h2>
      <p class="section-meta">Wie lange die Sollmengen reichen würden. Rechnet beim Bearbeiten mit.</p>
    </div>
    <dl class="pc-grid">
      <div class="pc-cell pc-main"><dt>Gesamt</dt><dd><span class="pc-num num" data-cover="days"><?= num($plan['days'], 1) ?></span> <span class="pc-unit">von <?= $h['days'] ?> Tagen</span></dd></div>
      <?php foreach ($cover as $k => $label): ?>
        <div class="pc-cell"><dt><?= e($label) ?></dt><dd><span class="pc-num num" data-cover="<?= $k ?>"><?= num($plan[$k], 1) ?></span> <span class="pc-unit">Tage</span></dd></div>
      <?php endforeach ?>
    </dl>
  </section>
  <?php foreach ($groups as $g): ?>
    <section class="plan-group" aria-labelledby="pg-<?= e($g['slug']) ?>">
      <div class="section-head">
        <h2 id="pg-<?= e($g['slug']) ?>"><?= e($g['name']) ?></h2>
        <?php if ($g['guideline']): ?><p class="section-meta">BBK: <?= e($g['guideline']) ?></p><?php endif ?>
      </div>
      <table class="table plan-table">
        <thead><tr>
          <th class="c-check" scope="col"><span class="visually-hidden">Im Plan</span></th>
          <th scope="col">Artikel</th>
          <th class="c-num" scope="col">Basis</th>
          <th class="c-num" scope="col">Empfehlung</th>
          <th class="c-num c-input" scope="col">Soll</th>
          <th class="c-num" scope="col">Ist</th>
          <th class="c-num c-kcal" scope="col">kcal/100</th>
        </tr></thead>
        <tbody>
          <?php foreach ($g['items'] as $it): ?>
            <?php $kind = coverage_kind($it) ?>
            <tr class="<?= $it['active'] ? '' : 'is-off' ?><?= $it['target_override'] !== null ? ' is-custom-target' : '' ?>" data-rec="<?= e((string) $it['recommended']) ?>"
                <?php if ($kind): ?>data-kind="<?= $kind ?>" data-kcal="<?= $it['kcal'] ?>" data-protein="<?= $it['protein'] ?>" data-fat="<?= $it['fat'] ?>" data-carbs="<?= $it['carbs'] ?>"<?php endif ?>>
              <td class="c-check"><label class="check"><input type="checkbox" name="active[]" value="<?= (int) $it['id'] ?>"<?= $it['active'] ? ' checked' : '' ?>><span class="visually-hidden"><?= e($it['name']) ?> im Plan</span></label></td>
              <th scope="row"><a href="<?= e(url('artikel/' . $it['id'])) ?>"><?= e($it['name']) ?></a><?php if ($it['is_custom']): ?> <span class="tag">eigener</span><?php endif ?></th>
              <td class="c-num c-base"><?= e(num($it['base_qty'], 3)) ?> <?= e(UNIT_LABEL[$it['unit']]) ?> <span class="scale"><?= e(SCALE_LABEL[$it['scale']]) ?></span></td>
              <td class="c-num"><?= e(qty($it['recommended'], $it['unit'])) ?></td>
              <td class="c-num c-input">
                <label class="visually-hidden" for="t-<?= (int) $it['id'] ?>">Soll <?= e($it['name']) ?> in <?= e(UNIT_LABEL[$it['unit']]) ?></label>
                <input class="input input-small num" id="t-<?= (int) $it['id'] ?>" name="target[<?= (int) $it['id'] ?>]" inputmode="decimal" value="<?= e(qty_input($it['target'], 2)) ?>">
                <span class="unit"><?= e(UNIT_LABEL[$it['unit']]) ?></span>
              </td>
              <td class="c-num"><?= e(qty($it['usable'], $it['unit'])) ?></td>
              <td class="c-num c-kcal"><?= $it['unit'] === 'stk' ? '–' : num($it['kcal'], 0) ?></td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </section>
  <?php endforeach ?>
  <div class="sticky-actions">
    <button class="btn btn-primary"><?= icon('check') ?><span>Planung speichern</span></button>
    <p class="plan-total" aria-live="polite">Reicht für <b class="num" data-cover="days"><?= num($plan['days'], 1) ?></b> von <?= $h['days'] ?> Tagen</p>
    <span class="hint">Abweichungen von der Empfehlung sind markiert.</span>
  </div>
</form>

<form method="post" class="reset-form" data-confirm="Alle Sollmengen auf die BBK-Empfehlung zurücksetzen und alle Standardartikel wieder einplanen?">
  <?= csrf_field() ?>
  <button class="btn btn-quiet" name="action" value="reset">Alles auf BBK-Empfehlung zurücksetzen</button>
</form>

<section class="add-item" id="neu" aria-labelledby="add-title">
  <div class="section-head"><h2 id="add-title">Eigenen Artikel ergänzen</h2><p class="section-meta">Zum Beispiel Babynahrung, Medikamente oder Lieblingskonserven.</p></div>
  <?php partial('item-form', ['item' => null, 'categories' => $categories, 'action' => 'add']) ?>
</section>
