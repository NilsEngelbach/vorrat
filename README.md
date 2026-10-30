# Notvorrat

Kleine PHP/MySQL-App, um den Notvorrat eines Haushalts zu planen und zu pflegen.

- **Übersicht**: das Kellerregal als Zeichnung. Gestrichelt ist, was fehlt, schraffiert, was da ist. Dazu die Versorgung in Tagen (Energie, Wasser, Eiweiß, Fett, Kohlenhydrate) und die Stückliste.
- **Empfehlung** nach BBK-Ratgeber, skaliert auf Erwachsene, Kinder, Haustiere und Zieldauer.
- **Planung**: Sollmengen überschreiben, Artikel abwählen, eigene Artikel mit Nährwerten anlegen.
- **Einlagern** mit Menge, MHD und Lagerort. Verbraucht wird immer die Charge mit dem frühesten MHD zuerst.
- **Haltbarkeit**: alle Chargen auf einer Zeitachse; Stufen „bald fällig“, „fällig“, „abgelaufen“.
- **Benachrichtigung** im Kopf der App und als tägliche Sammelmail (Cron).
- **Einkaufsliste** aus den Fehlmengen, in Packungen gerundet, druckbar.
- Geschützt durch ein gemeinsames Haushaltspasswort.

## Voraussetzungen

PHP 8.1+ mit PDO MySQL, MySQL 5.7+/MariaDB 10.3+, ein Cronjob für die Mail. Kein Composer, kein Build-Schritt.

## Installation

1. Ordner hochladen, als Document Root **`public/`** einstellen (Apache nutzt `public/.htaccess`; nginx: `try_files $uri $uri/ /index.php?$args;`).
2. `config.sample.php` nach `config.php` kopieren, Datenbank und `mail_from` eintragen.
3. Seite aufrufen. Die Tabellen werden beim ersten Aufruf angelegt, danach Passwort und Haushalt festlegen.
4. Unter *Einstellungen* mit „Test-Mail senden“ prüfen, ob der Versand klappt.
5. Cronjob einrichten (Befehl und Web-Cron-Link stehen unter *Einstellungen*):

   ```
   0 7 * * *  php /pfad/zu/notvorrat/bin/mhd-digest.php
   ```

## Lokale Entwicklung

Die Local-Site „notvorrat“ zeigt mit einer Verzeichnisverknüpfung auf dieses Repo:
`C:\Users\Nils\Local Sites\notvorrat\app\public` → `C:\DEV\notvorrat\public` (das ursprüngliche Verzeichnis liegt als `public.orig` daneben).
Mails landen in Mailpit (Local → Tools → Mailpit).

Neu anfangen: alle Tabellen mit Präfix `nv_` löschen, beim nächsten Aufruf wird neu installiert.

## Aufbau

```
public/          Webroot: index.php (Front-Controller), assets/ (CSS, JS, Schriften)
src/             bootstrap, routes, model, drawing (Regalzeichnung), mail, views/
src/catalog.php  BBK-Empfehlung mit Nährwerten
bin/             mhd-digest.php (Cron)
```

Schrift: Barlow (SIL Open Font License), lokal eingebunden.
