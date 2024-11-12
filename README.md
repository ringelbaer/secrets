# secrets
Übermittle Geheimnisse (Passwörter) über einen Link - auf deinem eigenen Webhost (PHP/sqlite3) - automatische Löschung - einmaliger (oder mehrmaliger) Abruf.

## Anwendungsbeispiel
Übermittle Credentials sicherer: statt Benutzername + Passwort sende Benutzername + Link an deinen Kontakt. Einmaliger Abruf des Geheimnisses über den Link ermöglicht Vertraulichkeit.

Kann der Empfänger das Passwort abrufen: alles in Ordnung. Niemand hat vorher das Passwort betrachtet.

Fordere den Empfänger trotzdem zur Passwortänderung auf!

## Don'ts
- Verschlüssele niemals in einem Geheimnis Benutzername + Passwort zusammen.
- Gib im Geheimnis niemals einen Hinweis auf den Zweck/Anwendungsort des Passworts.
- Nutze das Tool nicht ohne ausreichende Verbindungsverschlüsselung (SSL/https).

## Eigenschaften
- minimale Anforderungen: PHP8/sqlite3
- minimale Konfiguration: config.php
- keine Datenbankeinrichtung
- "Kopiere in die Zwischenablage"-Button für URL und Geheimnis
- eigenes Branding: logo.png
- extrem klein: keine Dependencies, Vanilla JS -> 15 KB + Logo + DB

## Sicherheit
- Verschlüsselung mit (halbwegs) zufälligem Schlüssel
- zufällige unique ID
- keine Speicherung des Schlüssels auf dem Server (dieser steht im Link)
- sofortige Löschung des Geheimnisses nach Abruf (bei einmaligem Abruf)
- automatische Löschung nach konfigurierbarem Zeitraum
- manuelle Löschmöglichkeit für mehrfach abrufbare Geheimnisse

## Installation
1. Lade die Dateien in ein eigenes Verzeichnis auf deinem Webserver.
2. Schaue dir die Einstellungen an, ob sie für dich passen.
3. ggf. tausche das Logo aus.
5. Fertig.

Hinweis:<br />
Mehrmals abrufbare Geheimnisse sind deutlich unsicherer als einmalig abrufbare Geheimnisse, da nicht ausgeschlossen werden kann, dass jemand Unberechtigtes, der vom Link Kenntnis erlangt, das Geheimnis abruft. Außerdem wird je nach Serverkonfiguration beim Abruf die URL inkl. Schlüssel in den Aufruf-Logs des Webservers protokolliert, was bei mehrmaliger Zugriffsmöglichkeit ebenfalls ein Risiko darstellt.

## Screenshots
![Screenshot 2024-02-18 220019](https://github.com/ringelbaer/secrets/assets/11261849/0d4e1510-c663-4a0e-9c34-709d90eece5a)
![Screenshot 2024-02-18 220032](https://github.com/ringelbaer/secrets/assets/11261849/f56a63b8-6a17-4074-b177-bab9c1547b5a)
![Screenshot 2024-02-18 220043](https://github.com/ringelbaer/secrets/assets/11261849/2be499dd-0e6f-44e2-9fef-dffe49ee1bfa)
![Screenshot 2024-02-18 220053](https://github.com/ringelbaer/secrets/assets/11261849/4e1e4692-b722-4f04-a8a5-454f9ab6a432)

