<?php
/**
 * Täglicher Cronjob für die MHD-Benachrichtigung, z. B.:
 *   0 7 * * *  php /pfad/zu/notvorrat/bin/mhd-digest.php
 * Alternativ ohne CLI-Zugang: https://<adresse>/cron/mhd?token=<Token aus den Einstellungen>
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit;
}
require dirname(__DIR__) . '/src/bootstrap.php';

$r = run_mhd_digest(in_array('--force', $argv, true));
echo date('Y-m-d H:i') . ' ' . $r['reason'] . " (neu: {$r['new']}, gesamt: {$r['total']})\n";
exit($r['sent'] || $r['new'] === 0 ? 0 : 1);
