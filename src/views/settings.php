<?php
/** @var array $h @var ?string $error @var string $cron_url */
$last = setting('last_digest_at');
?>
<div class="page-head">
  <h1>Einstellungen</h1>
</div>

<?php if ($error): ?><p class="flash flash-error" role="alert"><?= icon('warn') ?><span><?= e($error) ?></span></p><?php endif ?>

<div class="settings-grid">
  <section class="panel" aria-labelledby="s-notify">
    <h2 id="s-notify">MHD-Benachrichtigung</h2>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <label class="field"><span>E-Mail-Adresse</span><input class="input" type="email" name="notify_email" value="<?= e(setting('notify_email', '')) ?>" placeholder="haushalt@example.de" autocomplete="email"></label>
      <div class="form-row">
        <label class="field"><span>„Bald fällig“ ab … Tagen vorher</span><input class="input num" name="warn_days" inputmode="numeric" value="<?= $h['warn_days'] ?>"></label>
        <label class="field"><span>„Fällig“ ab … Tagen vorher</span><input class="input num" name="due_days" inputmode="numeric" value="<?= $h['due_days'] ?>"></label>
      </div>
      <p class="hint">Jede Charge wird pro Stufe einmal gemeldet: bald fällig, fällig, abgelaufen. Alle aktuellen Chargen kommen gesammelt in einer Mail.</p>
      <p class="hint">Die Test-Mail geht an die Adresse im Feld, auch wenn sie noch nicht gespeichert ist. Absender: <?= e((string) config('mail_from', '')) ?></p>
      <div class="form-actions">
        <button class="btn btn-primary" name="action" value="notify"><?= icon('check') ?><span>Speichern</span></button>
        <button class="btn" name="action" value="test"<?= setting('notify_email', '') === '' ? ' disabled' : '' ?>><?= icon('mail') ?><span>Sammelmail jetzt senden</span></button>
        <button class="btn btn-quiet" name="action" value="testmail" formnovalidate>Test-Mail senden</button>
      </div>
      <?php if ($last): ?><p class="hint num">Zuletzt gesendet: <?= e(date('d.m.Y, H:i', strtotime($last))) ?> Uhr</p><?php endif ?>
    </form>
  </section>

  <section class="panel" aria-labelledby="s-cron">
    <h2 id="s-cron">Täglicher Versand</h2>
    <p>Die Mail verschickt ein Cronjob. Einmal täglich reicht, zum Beispiel um 7 Uhr:</p>
    <pre class="code">0 7 * * *  php <?= e(str_replace('\\', '/', APP_ROOT)) ?>/bin/mhd-digest.php</pre>
    <p>Ohne Kommandozeile beim Hoster, als Web-Cron:</p>
    <pre class="code code-wrap"><?= e($cron_url) ?></pre>
    <form method="post" data-confirm="Neuen Link erzeugen? Der alte funktioniert danach nicht mehr.">
      <?= csrf_field() ?>
      <button class="btn btn-quiet" name="action" value="token">Neuen Cron-Link erzeugen</button>
    </form>
  </section>

  <section class="panel" aria-labelledby="s-pw">
    <h2 id="s-pw">Passwort</h2>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <label class="field"><span>Aktuelles Passwort</span><input class="input" type="password" name="current" autocomplete="current-password" required></label>
      <label class="field"><span>Neues Passwort</span><input class="input" type="password" name="new" autocomplete="new-password" minlength="8" required></label>
      <label class="field"><span>Neues Passwort wiederholen</span><input class="input" type="password" name="new2" autocomplete="new-password" minlength="8" required></label>
      <div class="form-actions"><button class="btn btn-primary" name="action" value="password"><?= icon('check') ?><span>Passwort ändern</span></button></div>
    </form>
  </section>

  <section class="panel" aria-labelledby="s-out">
    <h2 id="s-out">Sitzung</h2>
    <p>Angemeldet bleibt dieses Gerät <?= SESSION_DAYS ?> Tage. Haushalt und Zieldauer stehen unter <a href="<?= e(url('planung')) ?>">Planung</a>.</p>
    <form method="post" action="<?= e(url('logout')) ?>">
      <?= csrf_field() ?>
      <button class="btn"><?= icon('out') ?><span>Abmelden</span></button>
    </form>
  </section>
</div>
