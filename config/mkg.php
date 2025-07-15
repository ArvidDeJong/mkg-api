<?php

return [
    /*
    |--------------------------------------------------------------------------
    | MKG API Instellingen
    |--------------------------------------------------------------------------
    |
    | Deze instellingen worden gebruikt voor de verbinding met de MKG API.
    |
    */

    // API authenticatie URL
    'url_auth' => env('MKG_URL_AUTH', 'https://arvid.nl/auth'),

    // API productie URL
    'url_prod' => env('MKG_URL_PROD', 'https://arvid.nl'),

    // Klant ID
    'customer' => env('MKG_CUSTOMER'),

    // API gebruikersnaam
    'username' => env('MKG_USERNAME'),

    // API wachtwoord
    'password' => env('MKG_PASSWORD'),

    // Timeouts
    'timeout' => 30,
    'connect_timeout' => 10,

    // Cookie opslag locatie
    'cookie_path' => 'mkg/cookie.txt',
];
