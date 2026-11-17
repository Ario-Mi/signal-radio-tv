# GitHub Pages veroeffentlichen

Repository-Vorschlag: `signal-radio-tv` unter `Ario-Mi`.

1. Den Inhalt des bereinigten Release-Ordners hochladen, inklusive `.github/workflows/pages.yml` und aller Punktdateien.
2. Im Repository `Settings > Pages` oeffnen.
3. Unter `Build and deployment > Source` die Option `GitHub Actions` waehlen.
4. Unter `Actions` den Workflow `Publish SIGNAL Studio demo` starten, falls er noch nicht durch den Upload ausgefuehrt wurde (`Run workflow`).
5. Nach einem erfolgreichen Lauf zeigt GitHub die veroeffentlichte URL an. Bei dem vorgeschlagenen Namen lautet sie voraussichtlich `https://ario-mi.github.io/signal-radio-tv/`.

Die URL ist vor dem erstmaligen erfolgreichen Deployment kein bestaetigter Live-Link. Im README den Link anpassen, falls der Repositoryname anders ist.

Es wird ausschliesslich `demo/` veroeffentlicht. PHP-Dateien, Datenbankzugang und die echte Administration werden nicht an GitHub Pages ausgeliefert. Die Admin-Vorschau ist schreibgeschuetzt. Die Streams liegen bei externen Anbietern; Verfuegbarkeit, CORS und Geoblocking koennen die Wiedergabe beeinflussen.

Offizielle Anleitung: https://docs.github.com/en/pages/getting-started-with-github-pages/using-custom-workflows-with-github-pages
