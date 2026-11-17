# Ueberarbeitung und Pruefung

Stand: 17. November 2026.

## Sicherheit

- Gemeinsame Admin-Anmeldung fuer Radio und TV; Pruefung vor Datenbankzugriff und Aenderungen.
- Passwort-Hash aus lokaler Konfiguration oder Umgebungsvariable, kein festes Admin-Kennwort.
- Session-Erneuerung beim Login; Cookies mit HttpOnly, SameSite=Lax und Secure bei HTTPS.
- CSRF-Schutz fuer Admin-Formulare einschliesslich Login und Logout.
- Radio-Loeschaktionen von GET auf POST umgestellt. Verwendete Genres werden nicht geloescht.
- HTTP/HTTPS-URL-Pruefung, begrenzte Feldlaengen und sichere Behandlung von Texteingaben.
- Konfiguration und interne Hilfsdateien gegen direkte Apache-Aufrufe gesperrt.

## Kompatibilitaet

- Die oeffentlichen Player-Pfade und JSON-Endpunkte bleiben erhalten.
- Die vorhandenen Sender und Tabellen werden nicht migriert oder neu importiert.
- TV-Tabellen werden nicht mehr waehrend eines normalen Seitenaufrufs erstellt. Fuer neue Installationen gibt es schema.sql und optionale Demo-Daten.
- Die alten Radio-Formulare fuer Sender verwenden eine gemeinsame Implementierung; TV-Formulare behalten ihre Oberflaeche.
- Die WordPress-API-Adresse behaelt ihren bisherigen Standard und kann jetzt per Filter angepasst werden.
- JSON-Endpunkte melden auch Verbindungsprobleme als JSON statt als unerwarteten HTML-Text.

## Pruefung

Fuenf automatisierte HTTP-/Datenbanktests in einer temporaeren MariaDB-Instanz: Admin-Zugriffsschutz ohne Datenaenderung, Cookie/CSRF/Login/Logout, oeffentliche Player/API-Antworten, Radio-/Genre-CRUD samt Validierung sowie TV-CRUD und wiederholbare Demo-Ergaenzung.

Die echte Medienwiedergabe und die WordPress-Integration sind durch diese Tests nicht bestaetigt. Fuer den persoenlichen Admin-Zugang muss nach der Uebernahme einmal scripts/configure-admin.php ausgefuehrt werden.

## Einheitliches Design

Gemeinsamer Header, Navigation, Formular- und Tabellenstil fuer alle Admin-Seiten sowie eine oeffentliche Startseite mit Links zu Radio und TV. Radioverwaltung besitzt jetzt denselben dunklen Stil wie die Player. Sichtbare Textfehler wurden korrigiert; Fehlermeldungen und Rueckmeldungen sind einheitlich. Preview-Audio in der Administration startet mit 10 Prozent Lautstaerke. Ein zusaetzlicher Test prueft elf Seiten mit den zugehoerigen Stylesheets. Audio-/TV-Fehlermeldungen unterscheiden Verbindungsprobleme besser von Autoplay-Sperren.
