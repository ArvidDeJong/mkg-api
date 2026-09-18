# Changelog

Alle belangrijke wijzigingen aan de MKG package worden in dit bestand gedocumenteerd.

## [1.2.0] - 2026-09-18

Final release. This package is abandoned in favour of
[darvis/mkg-client](https://packagist.org/packages/darvis/mkg-client); see the
README for how to move. The code is unchanged.

### Changed

- `composer.json` marks the package as abandoned with `darvis/mkg-client` as the replacement, so Composer warns on install and update. The description, homepage and support links point at the replacement.
- The README opens with the deprecation notice and a migration table; the original Dutch documentation is kept below it.

## [1.1.0] - 2025-07-15

### Toegevoegd
- Uitgebreide logging van API requests en responses
- Betere foutafhandeling met gedetailleerde error messages
- HTTP status code vertalingen naar gebruiksvriendelijke foutmeldingen
- Automatische hernieuwing van sessie bij authenticatieproblemen
- Specifieke methodes voor POST en PUT requests
- Gedetailleerde documentatie voor alle publieke methodes

### Gewijzigd
- Verbeterde sessie cookie opslag en beheer
- Timeout en connection timeout configureerbaar gemaakt
- Code opschoning en verbeterde leesbaarheid
- Interne methoden gerefactored voor betere herbruikbaarheid

### Beveiliging
- Verbeterde foutrapportage zonder gevoelige informatie

## [1.0.0] - 2025-07-14

### Toegevoegd
- Initiële release van de package
- MKG service class voor API communicatie
- Config bestand met env-variabelen
- ServiceProvider voor Laravel integratie
- Facade voor eenvoudig gebruik
- Uitgebreide documentatie met installatievoorbeelden

### Gewijzigd
- Namespace veranderd van App\Services naar Darvis\Mkg
- Directe env() aanroepen vervangen door config() helper

### Beveiliging
- Inloggegevens worden gehaald uit .env via config
