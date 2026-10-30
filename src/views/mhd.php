<?php
/** @var array $h @var array $groups @var int $total @var int $wasted */
$sections = [
    'expired' => ['Abgelaufen', 'MHD überschritten. Prüfen: Viele Lebensmittel sind danach noch gut, sonst entsorgen und ersetzen.'],
    'due' => ['Fällig', "MHD in höchstens {$h['due_days']} Tagen. Jetzt verbrauchen und Ersatz kaufen."],
    'soon' => ['Bald fällig', "MHD in höchstens {$h['warn_days']} Tagen. In den Speiseplan einbauen."],
    'fresh' => ['Frisch', 'Kein Handlungsbedarf.'],
    'none' => ['Ohne MHD', 'Ausrüstung und Artikel ohne Datum.'],
];
$maxDays = 730;
?>
<div class="page-head">
  <h1>Haltbarkeit</h1>
  <p class="lede">Alle Chargen nach MHD. Die Balkenlänge ist die Zeit bis zum MHD, bis zwei Jahre im selben Maßstab.</p>
  <p class="page-meta num"><?= $total ?> Chargen · <?= count($groups['expired']) + count($groups['due']) + count($groups['soon']) ?> brauchen Aufmerksamkeit · <?= $wasted ?> im letzten Jahr entsorgt</p>
</div>

<?php if ($total === 0): ?>
  <p class="empty">Noch keine Chargen. Beim <a href="<?= e(url('einlagern')) ?>">Einlagern</a> das MHD eintragen, dann erscheint hier der Zeitplan.</p>
<?php endif ?>

<?php foreach ($sections as $key => [$label, $hint]): if (!$groups[$key]) continue; ?>
  <section class="mhd-group mhd-<?= $key ?>" aria-labelledby="mhd-<?= $key ?>">
    <div class="section-head">
      <h2 id="mhd-<?= $key ?>"><?= icon($key === 'fresh' || $key === 'none' ? 'check' : ($key === 'soon' ? 'clock' : 'warn')) ?><?= $label ?> <span class="count num"><?= count($groups[$key]) ?></span></h2>
      <p class="section-meta"><?= e($hint) ?></p>
    </div>
    <ul class="timeline timeline-wide" style="<?= timeline_track_vars($maxDays, $h['warn_days']) ?>">
      <?php if ($key !== 'none'): ?>
        <li class="tl-row tl-scale-row" aria-hidden="true">
          <span></span>
          <div class="tl-scale">
            <?php foreach ([0 => 'heute', 90 => '3 Mon.', 182 => '6 Mon.', 365 => '1 Jahr', 730 => '2 Jahre'] as $d => $lab): ?>
              <span style="--x: <?= round((30 + $d) / ($maxDays + 30) * 100, 3) ?>%"><?= $lab ?></span>
            <?php endforeach ?>
          </div>
          <span></span>
        </li>
      <?php endif ?>
      <?php foreach ($groups[$key] as $b): ?>
        <li class="tl-row state-<?= e($b['state']) ?>" style="<?= timeline_bar_vars($b['days'], $maxDays) ?>">
          <div class="tl-text">
            <a class="tl-name" href="<?= e(url('artikel/' . $b['item_id'])) ?>"><?= e($b['name']) ?></a>
            <span class="tl-qty num"><?= e(qty($b['qty'], $b['unit'])) ?></span>
            <span class="tl-mhd num"><?= $b['mhd'] ? e(date_de($b['mhd'])) : '–' ?></span>
            <span class="tl-state"><?= $b['mhd'] ? e(relative_days($b['days'])) : 'ohne MHD' ?><?= $b['location'] ? ' · ' . e($b['location']) : '' ?></span>
          </div>
          <?php if ($b['mhd']): ?><div class="tl-track" aria-hidden="true"><span class="tl-bar"></span><?php if ($b['days'] > $maxDays): ?><span class="tl-more" title="mehr als zwei Jahre"></span><?php endif ?></div><?php endif ?>
          <?php if (in_array($key, ['expired', 'due', 'soon'], true)): ?>
            <form class="tl-actions" method="post" action="<?= e(url('charge/' . $b['id'])) ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="back" value="haltbarkeit">
              <button class="btn btn-small" name="action" value="consume"><?= icon('minus') ?><span>Verbraucht</span></button>
              <?php if ($key !== 'soon'): ?><button class="btn btn-small btn-quiet" name="action" value="discard"><?= icon('trash') ?><span>Entsorgt</span></button><?php endif ?>
              <a class="btn btn-small btn-quiet" href="<?= e(url('einlagern', ['artikel' => $b['item_id']])) ?>"><?= icon('plus') ?><span>Ersatz</span></a>
            </form>
          <?php endif ?>
        </li>
      <?php endforeach ?>
    </ul>
  </section>
<?php endforeach ?>
