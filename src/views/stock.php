<?php
/** @var array $h @var array $groups @var array $batches */
echo drawing_defs();
?>
<div class="page-head">
  <h1>Vorrat</h1>
  <p class="lede">Jede Position mit ihren Chargen. Entnommen wird immer die Charge mit dem frühesten MHD zuerst.</p>
  <label class="filter">
    <?= icon('search') ?>
    <span class="visually-hidden">Vorrat durchsuchen</span>
    <input type="search" placeholder="Artikel suchen" data-filter=".stock-item" autocomplete="off">
  </label>
</div>

<?php foreach ($groups as $g): ?>
  <section class="stock-group" aria-labelledby="g-<?= e($g['slug']) ?>">
    <div class="section-head">
      <h2 id="g-<?= e($g['slug']) ?>"><?= e($g['name']) ?></h2>
      <?php if ($g['guideline']): ?><p class="section-meta">BBK: <?= e($g['guideline']) ?><?= str_contains($g['guideline'], 'kg') || str_contains($g['guideline'], ' l') ? ' je Person für 10 Tage' : '' ?></p><?php endif ?>
    </div>
    <ul class="stock-list">
      <?php foreach ($g['items'] as $it): $bs = $batches[$it['id']] ?? []; ?>
        <li class="stock-item state-<?= e($it['state']) ?>" data-name="<?= e(mb_strtolower($it['name'])) ?>">
          <div class="stock-row">
            <span class="callout<?= in_array($it['state'], ['due', 'expired'], true) ? ' callout-alert' : ($it['state'] === 'soon' ? ' callout-soon' : '') ?>"><?= $it['pos'] ?></span>
            <span class="stock-fig"><?= item_figure($it, 0.8) ?></span>
            <a class="stock-name" href="<?= e(url('artikel/' . $it['id'])) ?>"><?= e($it['name']) ?></a>
            <span class="stock-qty num"><b><?= e(qty($it['usable'], $it['unit'])) ?></b> / <?= e(qty($it['target'], $it['unit'])) ?></span>
            <span class="state-tag"><?= e(state_text($it)) ?></span>
            <a class="btn btn-small" href="<?= e(url('einlagern', ['artikel' => $it['id']])) ?>" aria-label="<?= e($it['name']) ?> einlagern"><?= icon('plus') ?><span>Einlagern</span></a>
          </div>
          <?php if ($bs): ?>
            <ul class="batch-list">
              <?php foreach ($bs as $b): ?>
                <li class="batch state-<?= e($b['state']) ?>">
                  <span class="batch-qty num"><?= e(qty($b['qty'], $it['unit'])) ?></span>
                  <span class="batch-mhd num"><?= $b['mhd'] ? 'MHD ' . e(date_de($b['mhd'])) : 'ohne MHD' ?></span>
                  <span class="batch-rel"><?= $b['mhd'] ? e(relative_days($b['days'])) : '' ?></span>
                  <span class="batch-loc"><?= e($b['location'] ?? '') ?></span>
                  <form class="batch-take" method="post" action="<?= e(url('charge/' . $b['id'])) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="back" value="vorrat">
                    <label class="visually-hidden" for="take-<?= $b['id'] ?>">Menge in <?= e(UNIT_LABEL[$it['unit']]) ?></label>
                    <input class="input input-small num" id="take-<?= $b['id'] ?>" name="qty" inputmode="decimal" value="<?= e(qty_input($it['pack_size'] && $it['pack_size'] < $b['qty'] ? $it['pack_size'] : $b['qty'])) ?>">
                    <span class="unit"><?= e(UNIT_LABEL[$it['unit']]) ?></span>
                    <button class="btn btn-small" name="action" value="consume"><?= icon('minus') ?><span>Verbraucht</span></button>
                    <?php if (in_array($b['state'], ['expired', 'due'], true)): ?>
                      <button class="btn btn-small btn-quiet" name="action" value="discard"><?= icon('trash') ?><span>Entsorgt</span></button>
                    <?php endif ?>
                  </form>
                </li>
              <?php endforeach ?>
            </ul>
          <?php endif ?>
        </li>
      <?php endforeach ?>
    </ul>
  </section>
<?php endforeach ?>
<p class="filter-empty" hidden>Kein Artikel passt zur Suche.</p>
