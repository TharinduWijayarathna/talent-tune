<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application base domain
    |--------------------------------------------------------------------------
    |
    | The base domain used for institution subdomains (e.g., institution-slug.{domain}).
    | Set APP_DOMAIN in .env (e.g. talenttune.com or talenttune.test for local).
    | When null, the app derives the base domain from the request host.
    |
    */
    'domain' => env('APP_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Reserved subdomains
    |--------------------------------------------------------------------------
    |
    | Subdomains that are not institution slugs (e.g. www, app, main app name).
    | Comma-separated in .env: APP_RESERVED_SUBDOMAINS=www,app,talenttune
    |
    */
    'reserved_subdomains' => array_filter(
        array_map('trim', explode(',', env('APP_RESERVED_SUBDOMAINS', 'www,app')))
    ),

    /*
    |--------------------------------------------------------------------------
    | Local development TLD
    |--------------------------------------------------------------------------
    |
    | When the request host ends with this TLD (e.g. .test), a two-part host
    | like "acme.test" is treated as having subdomain "acme". Set in .env as
    | APP_LOCAL_TLD (e.g. .test for Valet/local).
    |
    */
    'local_tld' => env('APP_LOCAL_TLD', '.test'),

];
