# Click-TT Club Manager 0.1.0

WordPress-Plugin für öffentliche myTischtennis/click-TT-Daten. Das Plugin ruft Seiten serverseitig mit der WordPress HTTP API ab, analysiert HTML-Tabellen, cached erfolgreiche Antworten und zeigt Daten mit Shortcodes an.

## Installation

1. `clicktt-club-manager.zip` in WordPress unter **Plugins > Installieren > Plugin hochladen** auswählen.
2. Plugin aktivieren.
3. Unter **Einstellungen > Click-TT** Vereinsname und myTischtennis-Mannschaftsseite prüfen.
4. Seite mit `[clicktt_mannschaften]` anlegen.
5. Bei Bedarf Gruppen-URLs als manuelle Quellen hinterlegen: `Erwachsene II | https://...`

## Shortcodes

- `[clicktt_mannschaften]`
- `[clicktt_tabelle team="Erwachsene"]`
- `[clicktt_spiele team="Erwachsene II" anzahl="10"]`
- `[clicktt_naechste_spiele team="Erwachsene" anzahl="5"]`
- `[clicktt_ergebnisse team="Erwachsene" anzahl="5"]`

`team` filtert gegen Anzeigename und URL. `anzahl` begrenzt die Ausgabe.

## Technik und Sicherheit

- Abruf nur von `mytischtennis.de` und `www.mytischtennis.de`
- `wp_safe_remote_get()` gegen SSRF
- 15 Sekunden Timeout und höchstens fünf Weiterleitungen
- Transient-Cache, standardmäßig 30 Minuten
- Bei Abruffehlern wird die zuletzt erfolgreiche Antwort verwendet
- Admin-Einstellungen erfordern `manage_options`
- Nonce beim Cache-Leeren
- Ausgabe konsequent escaped

## Hinweise

Das Plugin verwendet öffentlich sichtbare HTML-Seiten und keine offizielle API. Änderungen an HTML-Struktur oder Zugriffsschutz können den Parser beeinträchtigen. Abrufintervall moderat halten und Nutzungsbedingungen/Robots-Regeln des Anbieters beachten. Die Datenquelle sollte auf der Webseite sichtbar genannt und nach Möglichkeit verlinkt werden.

## Version 0.1.0

Erste funktionsfähige Basis mit Vereinsübersicht, automatischer Link-Erkennung, manuellen Fallback-Quellen, Tabellen, Spielplänen, nächsten Spielen, Ergebnissen, Cache und Elementor-kompatibler Akzentfarbe.
