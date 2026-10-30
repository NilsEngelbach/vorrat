<?php /** @var ?string $error */ ?>
<div class="gate-sheet">
  <div class="gate-drawing" aria-hidden="true">
    <svg viewBox="0 0 240 150" class="gate-rack"><defs><pattern id="g-hatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)"><path d="M0 0V4" stroke="#151515" stroke-width="1.4"/></pattern></defs><path class="r-line" d="M20 6V146M220 6V146M20 52H220M20 98H220M20 144H220"/><path class="r-dash" d="M34 52V26H58V52M66 52V32H88V52M98 52V20H112V52M126 98V74H150V98M160 98V68H176V98M34 144V116H74V144M84 144V120H108V144M180 144V112H206V144"/><path class="r-solid" d="M34 98V70H58V98M66 98V76H90V98M126 52V30H152V52M118 144V116H150V144"/><circle class="r-callout" cx="46" cy="12" r="7"/><path class="r-leader" d="M46 19V26"/></svg>
  </div>
  <div class="gate-form">
    <h1>Notvorrat</h1>
    <p class="lede">Bitte mit dem Haushaltspasswort anmelden.</p>
    <?php if ($error): ?><p class="flash flash-error" role="alert"><?= icon('warn') ?><span><?= e($error) ?></span></p><?php endif ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <label class="field"><span>Passwort</span><input class="input input-lg" type="password" name="password" autocomplete="current-password" required autofocus></label>
      <button class="btn btn-primary btn-lg"><?= icon('arrow') ?><span>Anmelden</span></button>
    </form>
  </div>
</div>
