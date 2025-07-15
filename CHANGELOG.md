# Changelog

Alle belangrijke wijzigingen aan de MKG package worden in dit bestand gedocumenteerd.

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
