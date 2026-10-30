<?php /** @var ?string $error */ ?>
<div class="gate-sheet gate-setup">
  <div class="gate-form">
    <h1>Notvorrat einrichten</h1>
    <p class="lede">Ein gemeinsames Passwort für den Haushalt, dazu wer versorgt werden soll. Daraus rechnet sich die BBK-Empfehlung. Alles lässt sich später unter Planung ändern.</p>
    <?php if ($error): ?><p class="flash flash-error" role="alert"><?= icon('warn') ?><span><?= e($error) ?></span></p><?php endif ?>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <div class="form-row">
        <label class="field"><span>Passwort</span><input class="input" type="password" name="password" autocomplete="new-password" minlength="8" required autofocus></label>
        <label class="field"><span>Passwort wiederholen</span><input class="input" type="password" name="password2" autocomplete="new-password" minlength="8" required></label>
      </div>
      <div class="form-row form-row-4">
        <label class="field"><span>Erwachsene</span><input class="input num" name="adults" inputmode="numeric" value="2" required></label>
        <label class="field"><span>Kinder</span><input class="input num" name="children" inputmode="numeric" value="0"></label>
        <label class="field"><span>Haustiere</span><input class="input num" name="pets" inputmode="numeric" value="0"></label>
        <label class="field"><span>Zieldauer (Tage)</span><input class="input num" name="target_days" inputmode="numeric" value="10"></label>
      </div>
      <label class="field"><span>Name des Haushalts <span class="optional">optional</span></span><input class="input" name="household_name" maxlength="60" placeholder="z. B. Familie Muster"></label>
      <p class="hint">Das BBK empfiehlt einen Vorrat für 10 Tage: 2 Liter Flüssigkeit und rund 2200 kcal je Person und Tag.</p>
      <button class="btn btn-primary btn-lg"><?= icon('check') ?><span>Notvorrat anlegen</span></button>
    </form>
  </div>
</div>
