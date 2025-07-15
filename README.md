# MKG CRM API Integratie voor Laravel

Deze Laravel package maakt het mogelijk om eenvoudig te communiceren met het MKG CRM systeem vanuit je Laravel applicatie.

## Contact

Voor vragen of ondersteuning bij het gebruik van deze package kunt u contact opnemen met:

Arvid de Jong  
E-mail: info@arvid.nl

## Documentatie MKG API

Voor meer informatie over de beschikbare endpoints en mogelijkheden van de MKG API, raadpleeg de officiële documentatie:

[MKG Kenniscentrum](https://www.mkg.eu/mijn-mkg/support/kenniscentrum?cat=845)

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
        $resultaat = $mkg->getLocation('pad/naar/resource');
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
        $resultaat = Mkg::getLocation('pad/naar/resource');
        return response()->json($resultaat);
    }
}
```

### Data ophalen

```php
// Gegevens ophalen
$data = $mkg->getLocation('endpoint/path');

// Controleren op fouten
if ($data === null) {
    $errors = $mkg->errors;
    // Doe iets met de errors
}
```

### Data wijzigen

```php
// Data wijzigen met PUT
$resultaat = $mkg->mutate('endpoint/path', [
    'veld1' => 'waarde1',
    'veld2' => 'waarde2'
], 'PUT');

// Data toevoegen met POST
$resultaat = $mkg->mutate('endpoint/path', [
    'veld1' => 'waarde1',
    'veld2' => 'waarde2'
], 'POST');
```
