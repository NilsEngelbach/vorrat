<?php
declare(strict_types=1);

const STAGE = ['soon' => 1, 'due' => 2, 'expired' => 3];

/**
 * Sendet eine Sammelmail, sobald eine Charge eine neue Stufe erreicht
 * (bald fällig → fällig → abgelaufen). Jede Stufe wird pro Charge nur einmal gemeldet.
 *
 * @return array{sent: bool, new: int, total: int, reason: string}
 */
function run_mhd_digest(bool $force = false): array
{
    $to = trim((string) setting('notify_email', ''));
    if ($to === '') {
        return ['sent' => false, 'new' => 0, 'total' => 0, 'reason' => 'Keine E-Mail-Adresse hinterlegt.'];
    }
    $batches = attention_batches();
    $new = array_filter($batches, fn($b) => STAGE[$b['state']] > (int) $b['notified_stage']);
    if (!$batches || (!$new && !$force)) {
        return ['sent' => false, 'new' => 0, 'total' => count($batches), 'reason' => 'Nichts Neues zu melden.'];
    }
    $newIds = array_column($new, 'id');
    $ok = send_digest($to, $batches, $newIds);
    if ($ok) {
        foreach ($batches as $b) {
            q('UPDATE nv_batches SET notified_stage = ? WHERE id = ?', [STAGE[$b['state']], $b['id']]);
        }
        set_setting('last_digest_at', date('Y-m-d H:i:s'));
    }
    return ['sent' => $ok, 'new' => count($new), 'total' => count($batches), 'reason' => $ok ? 'Gesendet.' : 'Versand fehlgeschlagen (mail()).'];
}

function send_digest(string $to, array $batches, array $newIds): bool
{
    $h = household();
    $title = 'Notvorrat' . ($h['name'] !== '' ? ' ' . $h['name'] : '');
    $counts = array_count_values(array_column($batches, 'state'));
    $subjectParts = [];
    foreach (['expired', 'due', 'soon'] as $s) {
        if (!empty($counts[$s])) {
            $subjectParts[] = $counts[$s] . ' ' . STATE_LABEL[$s];
        }
    }
    $subject = $title . ': ' . implode(', ', $subjectParts);
    $link = app_url() . '/haltbarkeit';

    $text = "Diese Chargen im Notvorrat erreichen bald ihr Mindesthaltbarkeitsdatum.\n"
          . "Am besten zuerst verbrauchen und gleich Ersatz einplanen.\n\n";
    $rows = '';
    foreach ($batches as $b) {
        $isNew = in_array($b['id'], $newIds, true) ? ' (neu)' : '';
        $line = sprintf('%-11s %-30s %10s  MHD %s, %s%s', STATE_LABEL[$b['state']], $b['name'], qty($b['qty'], $b['unit']), date_de($b['mhd']), relative_days($b['days']), $isNew);
        $text .= $line . "\n";
        $color = $b['state'] === 'fresh' ? '#151515' : '#c2410c';
        $rows .= '<tr>'
            . '<td style="padding:6px 12px 6px 0;border-bottom:1px solid #d5d7da;color:' . $color . ';font-weight:600;white-space:nowrap">' . e(STATE_LABEL[$b['state']]) . '</td>'
            . '<td style="padding:6px 12px 6px 0;border-bottom:1px solid #d5d7da">' . e($b['name']) . ($isNew ? ' <b>neu</b>' : '') . '</td>'
            . '<td style="padding:6px 12px 6px 0;border-bottom:1px solid #d5d7da;text-align:right;white-space:nowrap">' . e(qty($b['qty'], $b['unit'])) . '</td>'
            . '<td style="padding:6px 0;border-bottom:1px solid #d5d7da;white-space:nowrap">' . e(date_de($b['mhd'])) . ' · ' . e(relative_days($b['days'])) . '</td>'
            . '</tr>';
    }
    $text .= "\nZur Übersicht: {$link}\n";

    $html = '<!doctype html><html lang="de"><body style="margin:0;padding:24px;background:#fdfdfb;color:#151515;font:15px/1.45 Arial,Helvetica,sans-serif">'
        . '<div style="max-width:640px;border:2px solid #151515;padding:20px 24px">'
        . '<p style="margin:0 0 4px;font-size:20px;font-weight:700">' . e($title) . '</p>'
        . '<p style="margin:0 0 16px;color:#5b6168">Diese Chargen erreichen bald ihr Mindesthaltbarkeitsdatum. Am besten zuerst verbrauchen und Ersatz einplanen.</p>'
        . '<table style="border-collapse:collapse;width:100%">' . $rows . '</table>'
        . '<p style="margin:20px 0 0"><a href="' . e($link) . '" style="color:#151515;font-weight:700">Haltbarkeit öffnen →</a></p>'
        . '</div></body></html>';

    return send_mail($to, $subject, $text, $html, $title) === null;
}

/** Kurze Mail zum Prüfen der Versandeinstellungen. Gibt null zurück oder die Fehlermeldung. */
function send_test_mail(string $to): ?string
{
    $h = household();
    $title = 'Notvorrat' . ($h['name'] !== '' ? ' ' . $h['name'] : '');
    $from = (string) config('mail_from', 'notvorrat@localhost');
    $link = app_url();
    $sent = date('d.m.Y, H:i');

    $text = "Diese Test-Mail kam an, der Versand funktioniert.

"
          . "Absender: {$from}
Gesendet: {$sent} Uhr
Adresse der App: {$link}
";
    $html = '<!doctype html><html lang="de"><body style="margin:0;padding:24px;background:#fdfdfb;color:#151515;font:15px/1.45 Arial,Helvetica,sans-serif">'
        . '<div style="max-width:640px;border:2px solid #151515;padding:20px 24px">'
        . '<p style="margin:0 0 4px;font-size:20px;font-weight:700">' . e($title) . '</p>'
        . '<p style="margin:0 0 16px">Diese Test-Mail kam an, der Versand funktioniert.</p>'
        . '<p style="margin:0;color:#5b6168">Absender: ' . e($from) . '<br>Gesendet: ' . e($sent) . ' Uhr<br>'
        . 'Adresse der App: <a href="' . e($link) . '" style="color:#151515">' . e($link) . '</a></p>'
        . '</div></body></html>';

    return send_mail($to, $title . ': Test-Mail', $text, $html, $title);
}

/** Verschickt eine Mail als Text und HTML. Gibt null zurück oder die Fehlermeldung. */
function send_mail(string $to, string $subject, string $text, string $html, string $fromName): ?string
{
    $boundary = 'nv' . bin2hex(random_bytes(8));
    $from = (string) config('mail_from', 'notvorrat@localhost');
    $headers = [
        'From: ' . mb_encode_mimeheader($fromName, 'UTF-8') . ' <' . $from . '>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    $body = "--{$boundary}
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: base64

"
          . chunk_split(base64_encode($text))
          . "--{$boundary}
Content-Type: text/html; charset=UTF-8
Content-Transfer-Encoding: base64

"
          . chunk_split(base64_encode($html))
          . "--{$boundary}--
";
    // Envelope-Absender = From, sonst setzt der Hoster eine eigene Adresse und Mails landen eher im Spam.
    $params = filter_var($from, FILTER_VALIDATE_EMAIL) ? '-f' . $from : '';

    error_clear_last();
    if (@mail($to, mb_encode_mimeheader($subject, 'UTF-8'), $body, implode("
", $headers), $params)) {
        return null;
    }
    return error_get_last()['message'] ?? 'mail() hat den Versand abgelehnt.';
}
