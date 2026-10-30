<?php
/** @var ?array $item @var array $categories @var string $action */
$v = $item ?? ['category_id' => $categories[0]['id'] ?? 0, 'name' => '', 'unit' => 'kg', 'base_qty' => 0, 'scale' => 'person', 'pack_size' => null, 'pack_label' => '', 'kcal' => 0, 'protein' => 0, 'fat' => 0, 'carbs' => 0, 'shelf_months' => 12, 'shape' => 'box'];
$fid = $action . '-';
?>
<form method="post" class="item-form">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="<?= e($action) ?>">
  <div class="form-grid">
    <label class="field field-wide"><span>Benennung</span><input class="input" name="name" required maxlength="120" value="<?= e($v['name']) ?>"></label>
    <label class="field"><span>Gruppe</span>
      <select class="input" name="category_id">
        <?php foreach ($categories as $c): ?><option value="<?= (int) $c['id'] ?>"<?= (int) $c['id'] === (int) $v['category_id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach ?>
      </select>
    </label>
    <label class="field"><span>Form in der Zeichnung</span>
      <select class="input" name="shape">
        <?php foreach (SHAPES as $k => $label): ?><option value="<?= e($k) ?>"<?= $k === $v['shape'] ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach ?>
      </select>
    </label>
    <label class="field"><span>Einheit</span>
      <select class="input" name="unit">
        <?php foreach (['kg' => 'Kilogramm', 'l' => 'Liter', 'stk' => 'Stück'] as $k => $label): ?><option value="<?= $k ?>"<?= $k === $v['unit'] ? ' selected' : '' ?>><?= $label ?></option><?php endforeach ?>
      </select>
    </label>
    <label class="field"><span>Basismenge</span><input class="input num" name="base_qty" inputmode="decimal" value="<?= e(qty_input((float) $v['base_qty'])) ?>"></label>
    <label class="field"><span>Bezug</span>
      <select class="input" name="scale">
        <?php foreach (SCALE_LABEL as $k => $label): ?><option value="<?= $k ?>"<?= $k === $v['scale'] ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach ?>
      </select>
    </label>
    <label class="field"><span>Packungsgröße</span><input class="input num" name="pack_size" inputmode="decimal" value="<?= e(qty_input($v['pack_size'] !== null ? (float) $v['pack_size'] : null)) ?>"></label>
    <label class="field"><span>Packung (Text)</span><input class="input" name="pack_label" maxlength="60" placeholder="z. B. Dose 400 g" value="<?= e($v['pack_label'] ?? '') ?>"></label>
    <label class="field"><span>Haltbar (Monate)</span><input class="input num" name="shelf_months" inputmode="numeric" value="<?= (int) $v['shelf_months'] ?>"></label>
  </div>
  <fieldset class="form-grid nutrition-fields">
    <legend>Nährwerte je 100 g bzw. 100 ml</legend>
    <label class="field"><span>Energie (kcal)</span><input class="input num" name="kcal" inputmode="decimal" value="<?= e(num((float) $v['kcal'], 1)) ?>"></label>
    <label class="field"><span>Eiweiß (g)</span><input class="input num" name="protein" inputmode="decimal" value="<?= e(num((float) $v['protein'], 1)) ?>"></label>
    <label class="field"><span>Fett (g)</span><input class="input num" name="fat" inputmode="decimal" value="<?= e(num((float) $v['fat'], 1)) ?>"></label>
    <label class="field"><span>Kohlenhydrate (g)</span><input class="input num" name="carbs" inputmode="decimal" value="<?= e(num((float) $v['carbs'], 1)) ?>"></label>
  </fieldset>
  <div class="form-actions">
    <button class="btn btn-primary"><?= icon('check') ?><span><?= $action === 'add' ? 'Artikel anlegen' : 'Speichern' ?></span></button>
  </div>
</form>
