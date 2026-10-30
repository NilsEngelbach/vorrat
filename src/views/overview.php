<?php
/** @var array $h @var array $items @var array $groups @var array $cov @var array $plan @var array $attention @var array $log */
$relevant = array_filter($items, fn($i) => $i['relevant']);
$complete = count(array_filter($relevant, fn($i) => $i['ratio'] >= 0.999));
$dims = [
    ['Gesamt', $cov['days'], $plan['days']],
    ['Energie', $cov['food_days'], $plan['food_days']],
    ['Wasser', $cov['water_days'], $plan['water_days']],
    ['Eiweiß', $cov['protein_days'], $plan['protein_days']],
    ['Fett', $cov['fat_days'], $plan['fat_days']],
    ['Kohlenhydrate', $cov['carbs_days'], $plan['carbs_days']],
];
$max = max($h['days'] * 1.25, ...array_column($dims, 1), ...array_column($dims, 2));
$max = ceil($max / 5) * 5;
$limit = $cov['water_days'] < $cov['food_days'] ? 'Wasser' : 'Energie';
$planLimit = $plan['water_days'] < $plan['food_days'] ? 'Wasser' : 'Energie';
$hasStock = $cov['kcal'] > 0 || $cov['water_l'] > 0;
echo drawing_defs();
?>
<div class="sheet-head">
  <section class="dims" style="--max: <?= $max ?>; --soll: <?= $h['days'] ?>" aria-labelledby="dims-title">
    <h1 id="dims-title" class="visually-hidden">Versorgung in Tagen</h1>
    <div class="dim-hero">
      <p class="dim-figure"><span class="dim-num num"><?= num($cov['days'], 1) ?></span><span class="dim-unit">Tage versorgt</span></p>
      <p class="dim-note">
        <?php if (!$hasStock): ?>
          Noch nichts eingelagert. Das Regal zeigt gestrichelt, was nach BBK-Empfehlung hineingehört.
        <?php elseif ($cov['days'] >= $h['days']): ?>
          Ziel erreicht. Am knappsten ist <?= $limit ?>.
        <?php else: ?>
          Am knappsten ist <?= $limit ?>. Bis zum Ziel fehlen <?= num($h['days'] - $cov['days'], 1) ?> Tage.
        <?php endif ?>
      </p>
      <p class="dim-note">
        <?php if ($plan['days'] >= $h['days'] - 0.05): ?>
          Der Plan reicht für <b class="num"><?= num($plan['days'], 1) ?> Tage</b>, gestrichelt.
        <?php else: ?>
          Der Plan selbst reicht nur für <b class="num"><?= num($plan['days'], 1) ?> Tage</b>, knapp ist <?= $planLimit ?>.
        <?php endif ?>
        <a class="link-arrow" href="<?= e(url('planung')) ?>">Planung <?= icon('arrow') ?></a>
      </p>
    </div>
    <div class="dims-plot">
      <div class="dims-soll" aria-hidden="true"><span>Soll <?= $h['days'] ?> Tage</span></div>
      <div class="dims-cols" aria-hidden="true"><span>Vorrat</span><span>Plan</span></div>
      <ul class="dim-rows">
        <?php foreach ($dims as $k => [$label, $v, $p]): ?>
          <li class="dim<?= $k === 0 ? ' dim-main' : '' ?><?= $v < 0.05 ? ' is-zero' : '' ?><?= $p < 0.05 ? ' is-plan-zero' : '' ?><?= $v >= $h['days'] ? ' is-met' : '' ?>" style="--v: <?= round(min($v, $max), 2) ?>; --p: <?= round(min($p, $max), 2) ?>">
            <span class="dim-label"><?= e($label) ?></span>
            <span class="dim-track"><span class="dim-plan"></span><span class="dim-line"></span></span>
            <span class="dim-val num"><?= num($v, 1) ?><span class="visually-hidden"> Tage im Vorrat,</span></span>
            <span class="dim-pval num"><span class="visually-hidden">laut Plan </span><?= num($p, 1) ?></span>
          </li>
        <?php endforeach ?>
      </ul>
      <div class="dims-scale" aria-hidden="true">
        <?php for ($t = 0; $t <= $max; $t += ($max > 30 ? 10 : 5)): ?>
          <span style="--t: <?= $t ?>"><?= $t ?></span>
        <?php endfor ?>
        <span class="dims-scale-unit">Tage</span>
      </div>
    </div>
  </section>

  <aside class="titleblock" aria-label="Schriftfeld">
    <div class="tb-cell tb-wide"><span class="tb-label">Benennung</span><span class="tb-value tb-title">Notvorrat<?= $h['name'] !== '' ? ' ' . e($h['name']) : '' ?></span></div>
    <div class="tb-cell"><span class="tb-label">Haushalt</span><span class="tb-value"><?= e(household_label($h)) ?></span></div>
    <div class="tb-cell"><span class="tb-label">Ziel</span><span class="tb-value"><?= $h['days'] ?> Tage</span></div>
    <div class="tb-cell"><span class="tb-label">Positionen komplett</span><span class="tb-value num"><?= $complete ?> / <?= count($relevant) ?></span></div>
    <div class="tb-cell"><span class="tb-label">Stand</span><span class="tb-value"><?= e(date_de(today(), true)) ?></span></div>
    <a class="tb-cell tb-action" href="<?= e(url('einlagern')) ?>">
      <span class="tb-value">Einlagern <?= icon('arrow') ?></span>
    </a>
  </aside>
</div>

<div class="shelf-zone">
  <section class="shelf" aria-labelledby="shelf-title">
    <div class="section-head">
      <h2 id="shelf-title">Regal</h2>
      <p class="legend">
        <span class="lg lg-stock">vorhanden</span>
        <span class="lg lg-missing">fehlt</span>
        <span class="lg lg-doubled">bald fällig</span>
        <span class="lg lg-expired">abgelaufen</span>
      </p>
    </div>
    <div class="rack">
      <?php $bi = 0; foreach ($groups as $g):
          $gTarget = array_sum(array_column($g['items'], 'target'));
          $units = array_unique(array_column($g['items'], 'unit'));
          ?>
        <div class="board<?= $g['slug'] === 'getraenke' ? ' board-floor' : '' ?>" style="--i: <?= $bi++ ?>">
          <p class="board-label"><span><?= e($g['name']) ?></span>
            <?php if (count($units) === 1 && $units[0] !== 'stk'): ?>
              <span class="board-sum num"><?= e(qty(array_sum(array_column($g['items'], 'usable')), $units[0])) ?> von <?= e(qty($gTarget, $units[0])) ?></span>
            <?php endif ?>
          </p>
          <div class="board-items">
            <?php foreach ($g['items'] as $it): ?>
              <a class="slot state-<?= e($it['state']) ?><?= (int) ($_GET['neu'] ?? 0) === (int) $it['id'] ? ' is-new' : '' ?>" href="<?= e(url('artikel/' . $it['id'])) ?>" data-pos="<?= $it['pos'] ?>"
                 aria-label="Position <?= $it['pos'] ?>: <?= e($it['name']) ?>, <?= e(qty($it['usable'], $it['unit'])) ?> von <?= e(qty($it['target'], $it['unit'])) ?>, <?= e(state_text($it)) ?>">
                <span class="callout<?= in_array($it['state'], ['due', 'expired'], true) ? ' callout-alert' : ($it['state'] === 'soon' ? ' callout-soon' : '') ?>"><?= $it['pos'] ?></span>
                <span class="fig-area"><?= item_figure($it, 1.3) ?></span>
                <span class="slot-cap">
                  <span class="slot-name"><?= e($it['name']) ?></span>
                  <span class="slot-fill num"><?= e(fill_caption($it)) ?><?php if (pack_plan($it)['per'] > 1): ?> <span class="slot-per">· 1 Symbol = <?= pack_plan($it)['per'] ?></span><?php endif ?></span>
                  <?php if ($it['state'] !== 'fresh'): ?><span class="slot-state"><?= e(state_text($it)) ?></span><?php endif ?>
                </span>
              </a>
            <?php endforeach ?>
          </div>
        </div>
      <?php endforeach ?>
    </div>
  </section>

  <aside class="notes" aria-labelledby="notes-title">
    <h2 id="notes-title">Hinweise</h2>
    <?php if (!$attention): ?>
      <p class="notes-empty">Keine Charge erreicht in den nächsten <?= $h['warn_days'] ?> Tagen ihr MHD.</p>
    <?php else: ?>
      <ol class="note-list">
        <?php foreach ($attention as $b): ?>
          <li class="note state-<?= e($b['state']) ?>">
            <p class="note-head"><span class="note-state"><?= icon($b['state'] === 'soon' ? 'clock' : 'warn') ?><?= e(STATE_LABEL[$b['state']]) ?></span><span class="note-date num"><?= e(date_de($b['mhd'])) ?></span></p>
            <p class="note-body"><a href="<?= e(url('artikel/' . $b['item_id'])) ?>"><?= e($b['name']) ?></a>, <?= e(qty($b['qty'], $b['unit'])) ?>. MHD <?= e(relative_days($b['days'])) ?>.</p>
          </li>
        <?php endforeach ?>
      </ol>
      <a class="link-arrow" href="<?= e(url('haltbarkeit')) ?>">Alle Haltbarkeiten <?= icon('arrow') ?></a>
    <?php endif ?>

    <h2 class="notes-sub">Zuletzt</h2>
    <?php if (!$log): ?>
      <p class="notes-empty">Noch keine Bewegungen.</p>
    <?php else: ?>
      <ul class="log-list">
        <?php foreach ($log as $l): ?>
          <li><span class="log-sign log-<?= e($l['type']) ?>"><?= $l['type'] === 'in' ? '+' : '−' ?></span><span class="log-name"><?= e($l['name']) ?></span><span class="num"><?= e(qty((float) $l['qty'], $l['unit'])) ?></span></li>
        <?php endforeach ?>
      </ul>
    <?php endif ?>
  </aside>
</div>

<section class="parts" aria-labelledby="parts-title">
  <div class="section-head">
    <h2 id="parts-title">Stückliste</h2>
    <a class="link-arrow" href="<?= e(url('einkaufsliste')) ?>">Fehlmengen als Einkaufsliste <?= icon('arrow') ?></a>
  </div>
  <table class="table parts-table">
    <thead>
      <tr><th class="c-pos" scope="col">Pos.</th><th scope="col">Benennung</th><th class="c-num" scope="col">Soll</th><th class="c-num" scope="col">Ist</th><th class="c-num" scope="col">Fehlt</th><th scope="col">Nächstes MHD</th><th scope="col">Zustand</th></tr>
    </thead>
    <?php foreach ($groups as $g): ?>
      <tbody>
        <tr class="row-group"><th colspan="7" scope="rowgroup"><?= e($g['name']) ?></th></tr>
        <?php foreach ($g['items'] as $it): ?>
          <tr data-pos="<?= $it['pos'] ?>" class="state-<?= e($it['state']) ?>">
            <td class="c-pos"><span class="callout callout-sm<?= in_array($it['state'], ['due', 'expired'], true) ? ' callout-alert' : ($it['state'] === 'soon' ? ' callout-soon' : '') ?>"><?= $it['pos'] ?></span></td>
            <td><a href="<?= e(url('artikel/' . $it['id'])) ?>"><?= e($it['name']) ?></a></td>
            <td class="c-num"><?= e(qty($it['target'], $it['unit'])) ?></td>
            <td class="c-num"><?= e(qty($it['usable'], $it['unit'])) ?></td>
            <td class="c-num<?= $it['gap'] > 0 ? ' is-gap' : '' ?>"><?= $it['gap'] > 0 ? e(qty($it['gap'], $it['unit'])) : '–' ?></td>
            <td class="num"><?= $it['next_mhd'] ? e(date_de($it['next_mhd'])) : '–' ?></td>
            <td><span class="state-tag"><?= e(state_text($it)) ?></span></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    <?php endforeach ?>
  </table>
</section>
