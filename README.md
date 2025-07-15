# MKG CRM API Integratie voor Laravel

Deze Laravel package maakt het mogelijk om eenvoudig te communiceren met het MKG CRM systeem vanuit je Laravel applicatie.

![Versie](https://img.shields.io/badge/versie-1.1.0-blue) ![Laravel](https://img.shields.io/badge/laravel-12-red)

## Nieuwe Features in v1.1.0

- Verbeterde foutafhandeling en logging
- Automatische sessie hernieuwing
- Nieuwe handige methodes voor POST en PUT requests
- Configureerbare timeouts

## Contact

Voor vragen of ondersteuning bij het gebruik van deze package kunt u contact opnemen met:

Arvid de Jong  
E-mail: info@arvid.nl

## Documentatie MKG API

Voor meer informatie over de beschikbare endpoints en mogelijkheden van de MKG API, raadpleeg de officiële documentatie:

[MKG Kenniscentrum](https://www.mkg.eu/mijn-mkg/support/kenniscentrum?cat=845)

## Changelog

Voor een compleet overzicht van alle wijzigingen, bekijk de [CHANGELOG.md](CHANGELOG.md).

## Installatie

Je kunt de package installeren via Composer:

```bash
composer require darvis/mkg
```

Publiceer daarna de configuratie:

```bash
php artisan vendor:publish --tag=mkg-config
```

## Configuratie

Na het publiceren van de configuratie, kun je de instellingen vinden in `config/mkg.php`. Je kunt ook de volgende omgevingsvariabelen instellen in je `.env` bestand:

```
# LET OP: Dit zijn allemaal voorbeeldwaardes! Vervang deze door je eigen gegevens
MKG_URL_AUTH=https://arvid.nl/mkg/static/auth/j_spring_security_check
MKG_URL_PROD=https://arvid.nl/mkg/rest/v1/MKG/Documents
MKG_CUSTOMER=45432326-e397-4da6-a27e-d9e332ff2a00
MKG_USERNAME=mkguser
MKG_PASSWORD=mkgpassword

# Optionele configuratie
MKG_TIMEOUT=30        # Request timeout in seconden (standaard: 30)
MKG_CONNECT_TIMEOUT=10 # Connection timeout in seconden (standaard: 10)
MKG_COOKIE_PATH=mkg/cookie.txt # Pad voor sessie cookie opslag
```

**Belangrijk**: Bovenstaande waarden zijn slechts voorbeelden. Gebruik je eigen inloggegevens en URLs verstrekt door MKG.

## Gebruik

### Via Dependency Injection

```php
use Darvis\Mkg\Mkg;

class MijnController extends Controller
{
    public function index(Mkg $mkg)
    {
        $resultaat = $mkg->get('pad/naar/resource');
        return response()->json($resultaat);
    }
}
```

### Via Facade

```php
use Darvis\Mkg\Facades\Mkg;

class MijnController extends Controller
{
    public function index()
    {
        $resultaat = Mkg::get('pad/naar/resource');
        return response()->json($resultaat);
    }
}
```

### Data ophalen

```php
// Gegevens ophalen
$data = $mkg->get('endpoint/path');

// Controleren op fouten
if ($data === null) {
    $errors = $mkg->errors;
    // Toegang tot alle foutmeldingen
    foreach ($errors as $error) {
        Log::error("MKG Error: {$error}");
    }
}
```

### Data wijzigen

```php
// Data wijzigen met PUT (nieuwe methode)
$resultaat = $mkg->put('endpoint/path', [
    'veld1' => 'waarde1',
    'veld2' => 'waarde2'
]);

// Data toevoegen met POST (nieuwe methode)
$resultaat = $mkg->post('endpoint/path', [
    'veld1' => 'waarde1',
    'veld2' => 'waarde2'
]);

// Of gebruik de generieke mutate methode voor meer controle
$resultaat = $mkg->mutate('endpoint/path', [
    'veld1' => 'waarde1',
    'veld2' => 'waarde2'
], 'PUT');
```

## Gespecialiseerde Modules

De MKG package bevat gespecialiseerde modules voor specifieke onderdelen van het MKG CRM systeem.

### MkgInvoice

De `MkgInvoice` klasse biedt specifieke functionaliteit voor het werken met facturen in het MKG systeem.

```php
use Darvis\Mkg\MkgInvoice;

// Via Dependency Injection in een controller
public function getInvoice(MkgInvoice $invoice, string $invoice_nr)
{
    $factuur = $invoice->get($invoice_nr);

    if ($factuur) {
        // Toegang tot factuurgegevens
        $factuurnummer = $factuur['opde_boekstuk'] ?? null;
        $openstaandBedrag = $factuur['opde_bdr_open_ov'] ?? 0;
        $factuurDatum = $factuur['opde_dat_factuur'] ?? null;
        $vervaldatum = $factuur['opde_dat_verval'] ?? null;
        $isBetaald = $factuur['opde_afgewerkt'] === 'Ja';

        return response()->json([
            'factuurnummer' => $factuurnummer,
            'openstaand_bedrag' => $openstaandBedrag,
            'datum' => $factuurDatum,
            'vervaldatum' => $vervaldatum,
            'is_betaald' => $isBetaald
        ]);
    }

    return response()->json(['error' => 'Factuur niet gevonden'], 404);
}
```

#### Beschikbare Velden

De `MkgInvoice` klasse bevat een gestructureerde `fieldDescriptions` array met uitleg over alle factuurvelden en hun formaten:

| Veldnaam            | Omschrijving                       | Formaat         |
| ------------------- | ---------------------------------- | --------------- |
| opde_boekstuk       | Boekstuk/factuurnummer             | varchar         |
| opde_afgewerkt      | Of de factuur is afgewerkt/betaald | ja/nee          |
| opde_bdr_open_ov    | Openstaand bedrag                  | >>>,>>>,>>9.99- |
| opde_bet_wijze      | Betaalwijze                        | int             |
| opde_dat_factuur    | Factuurdatum                       | 99-99-9999      |
| opde_dat_verval     | Vervaldatum                        | 99-99-9999      |
| opde_dat_ingave     | Datum van ingave                   | 99-99-9999      |
| opde_dat_historisch | Datum historie afgehandeld/betaald | 99-99-9999      |

##### Helper Methodes

De `MkgInvoice` klasse biedt handige methodes om met de veldstructuur te werken:

```php
// Alleen veldnamen en omschrijvingen ophalen
$descriptions = $invoice->getSimpleFieldDescriptions();

// Alleen veldnamen en hun formaten ophalen
$formats = $invoice->getFieldFormats();

// Direct toegang tot individuele velden
$format = $invoice->fieldDescriptions['opde_boekstuk']['format']; // 'varchar'
$description = $invoice->fieldDescriptions['opde_boekstuk']['description']; // 'Boekstuk/factuurnummer'
```
