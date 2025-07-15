<?php

namespace Darvis\Mkg;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

/**
 * MKG CRM Integratie voor Laravel
 *
 * Deze klasse verzorgt de communicatie met het MKG CRM systeem via de REST API.
 * Ondersteunt authenticatie, data ophalen (GET) en data muteren (POST, PUT).
 *
 * @package Darvis\Mkg
 * @author Arvid de Jong <info@arvid.nl>
 */
class Mkg
{
    private const TIMEOUT = 30;
    private const CONNECT_TIMEOUT = 10;
    private const HTTP_OK = 200;
    private const HTTP_REDIRECT = 302;
    private const HTTP_ERROR_MESSAGES = [
        400 => 'Ongeldige aanvraag',
        401 => 'Niet geautoriseerd',
        403 => 'Geen toegang',
        404 => 'Niet gevonden',
        500 => 'Server fout',
        503 => 'Service niet beschikbaar'
    ];

    public array $errors = [];
    private Client $client;
    private ?string $sessionId = null;

    /**
     * Initialiseer de MKG client met configuratie uit config/mkg.php
     * en probeert een bestaande sessie te laden of een nieuwe aan te maken.
     */
    public function __construct()
    {
        $this->client = new Client([
            'verify' => false,
            'timeout' => Config::get('mkg.timeout', self::TIMEOUT),
            'connect_timeout' => Config::get('mkg.connect_timeout', self::CONNECT_TIMEOUT)
        ]);

        // Try to get existing session
        $this->sessionId = $this->getSessionCookie();
        if (!$this->sessionId) {
            $this->login();
            $this->sessionId = $this->getSessionCookie();
        }
    }

    /**
     * Inloggen bij de MKG API
     * 
     * Gebruikt de inloggegevens uit de config en slaat de sessie cookie op voor toekomstige requests
     * 
     * @throws \Exception Als het inloggen mislukt
     * @return void
     */
    public function login(): void
    {
        try {
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json',
                'X-CustomerID' => Config::get('mkg.customer')
            ];

            $formParams = [
                'j_username' => Config::get('mkg.username'),
                'j_password' => Config::get('mkg.password'),
            ];

            $response = $this->client->post(Config::get('mkg.url_auth'), [
                'headers' => $headers,
                'form_params' => $formParams,
                'allow_redirects' => false,
            ]);

            if (!in_array($response->getStatusCode(), [self::HTTP_OK, self::HTTP_REDIRECT])) {
                throw new \Exception($this->getErrorMessage($response->getStatusCode()));
            }

            $cookieString = $this->extractCookies($response->getHeader('Set-Cookie'));
            Storage::put(Config::get('mkg.cookie_path', 'mkg/cookie.txt'), $cookieString);
            Log::info('MKG: Succesvol ingelogd');
        } catch (\Exception $e) {
            $this->logError('Login error', $e);
            throw $e; // Re-throw to handle in caller
        }
    }

    // Get data
    /**
     * Haal gegevens op van de MKG API (GET)
     *
     * @param string $location Het API endpoint pad
     * @return array|null De opgehaalde data of null bij een fout (check de $errors eigenschap voor foutmeldingen)
     */
    public function get(string $location)
    {
        try {
            if (!$this->sessionId) {
                $this->login();
                $this->sessionId = $this->getSessionCookie();
            }

            if (!$this->sessionId) {
                throw new \Exception('Kon niet inloggen bij MKG');
            }

            $response = $this->client->get(Config::get('mkg.url_prod') . $location, [
                'headers' => [
                    'Cookie' => "JSESSIONID={$this->sessionId}",
                    'X-CustomerID' => Config::get('mkg.customer'),
                    'Accept' => 'application/json'
                ]
            ]);

            if ($response->getStatusCode() === self::HTTP_OK) {
                $data = json_decode($response->getBody()->getContents(), true);
                Log::info('MKG Response', ['data' => $data]);
                return $data;
            }

            throw new \Exception($this->getErrorMessage($response->getStatusCode()));
        } catch (\Exception $e) {
            $this->logError('Get location error', $e);
            // Als er een auth error is, probeer opnieuw in te loggen
            if ($e->getCode() === 401) {
                Storage::delete('mkg/cookie.txt');
                $this->sessionId = null;
                return $this->get($location);
            }
            return null;
        }
    }

    // New data
    /**
     * Voeg nieuwe gegevens toe aan de MKG API (POST)
     *
     * @param string $location Het API endpoint pad
     * @param array $array De te versturen gegevens
     * @return array|null Het resultaat van de API call of null bij een fout
     */
    public function post(string $location, array $array)
    {
        $this->mutate($location,  $array, 'POST');
    }

    // Update data
    /**
     * Werk bestaande gegevens bij in de MKG API (PUT)
     *
     * @param string $location Het API endpoint pad
     * @param array $array De te versturen gegevens
     * @return array|null Het resultaat van de API call of null bij een fout
     */
    public function put(string $location, array $array)
    {
        $this->mutate($location,  $array, 'PUT');
    }

    /**
     * Voer een mutatie uit op de MKG API (PUT of POST)
     * 
     * Deze methode wordt intern gebruikt door de post() en put() methoden
     * maar kan ook direct worden aangeroepen voor andere HTTP methoden
     *
     * @param string $location Het API endpoint pad
     * @param array $array De te versturen gegevens
     * @param string $type Het HTTP request type (PUT, POST, etc)
     * @return array|null Het resultaat van de API call of null bij een fout
     */
    public function mutate(string $location, array $array, string $type = 'PUT')
    {
        try {
            if (!$this->sessionId) {
                $this->login();
                $this->sessionId = $this->getSessionCookie();
            }

            if (!$this->sessionId) {
                throw new \Exception('Kon niet inloggen bij MKG');
            }

            $url = Config::get('mkg.url_prod') . $location;

            $options = [
                'headers' => [
                    'Cookie' => "JSESSIONID={$this->sessionId}",
                    'X-CustomerID' => Config::get('mkg.customer'),
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => $array
            ];

            Log::debug("MKG Request ({$type}): {$url}", ['body' => $array]);

            $response = $this->client->request($type, $url, $options);
            $statusCode = $response->getStatusCode();

            if ($statusCode >= 400) {
                throw new \Exception($this->getErrorMessage($statusCode));
            }

            $responseData = json_decode($response->getBody()->getContents(), true);
            Log::info("MKG Success ({$type}): {$location}", ['status' => $statusCode]);

            return $responseData;
        } catch (\Exception $e) {
            $this->logError("Mutate error ({$type} {$location})", $e);

            // Als er een auth error is, probeer opnieuw in te loggen
            if ($e->getCode() === 401) {
                Storage::delete('mkg/cookie.txt');
                $this->sessionId = null;
                return $this->mutate($location, $array, $type);
            }

            return null;
        }
    }



    /**
     * Haalt de opgeslagen sessie cookie op uit storage
     * 
     * @return string|null De JSESSIONID als deze gevonden is, anders null
     */
    private function getSessionCookie(): ?string
    {
        if (Storage::exists(Config::get('mkg.cookie_path', 'mkg/cookie.txt'))) {
            $cookie = Storage::get(Config::get('mkg.cookie_path', 'mkg/cookie.txt'));
            if (preg_match('/JSESSIONID=([^;]+)/', $cookie, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    /**
     * Extraheert de JSESSIONID uit de response cookies
     * 
     * @param array $cookies De cookies uit de HTTP response
     * @return string De complete cookie string
     * @throws \Exception Als de JSESSIONID niet gevonden kan worden
     */
    private function extractCookies(array $cookies): string
    {
        foreach ($cookies as $cookie) {
            if (strpos($cookie, 'JSESSIONID') !== false) {
                return $cookie;
            }
        }
        throw new \Exception('Geen JSESSIONID gevonden in response');
    }

    /**
     * Logt fouten naar het Laravel log systeem en houdt ze bij in de $errors array
     * 
     * @param string $context Context informatie over waar de fout optrad
     * @param \Exception $e De exception die werd gevangen
     * @return void
     */
    private function logError(string $context, \Exception $e): void
    {
        Log::error("MKG Error - {$context}", [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'trace' => $e->getTraceAsString()
        ]);
        $this->errors[] = $e->getMessage();
    }

    /**
     * Vertaal een HTTP status code naar een leesbare foutmelding
     * 
     * @param int $statusCode De HTTP status code
     * @return string De bijbehorende foutmelding
     */
    private function getErrorMessage(int $statusCode): string
    {
        return self::HTTP_ERROR_MESSAGES[$statusCode] ?? "Onbekende fout (status {$statusCode})";
    }
}
