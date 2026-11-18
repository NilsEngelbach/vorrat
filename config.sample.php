<?php
// Kopieren nach config.php und anpassen.
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => '',
        'user' => '',
        'pass' => '',
    ],
    // Optional. Die Adresse ergibt sich sonst aus dem Browseraufruf; nur nötig, wenn der
    // Cronjob per Kommandozeile läuft, bevor die App je im Browser geöffnet wurde.
    'base_url'  => '',
    // Absender der MHD-Benachrichtigungen. Sollte beim Hoster als Postfach existieren.
    'mail_from' => 'notvorrat@example.de',
    'timezone'  => 'Europe/Berlin',
    // Nur für die Entwicklung: Fehler im Browser anzeigen.
    'debug'     => false,
];
