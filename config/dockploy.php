<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dockploy / Vintorr Panel API
    |--------------------------------------------------------------------------
    |
    | Used in production to create subdomains (e.g. {institution-slug}.talenttune.site)
    | when an institution is activated. Set DOCKPLOY_TOKEN and DOCKPLOY_APPLICATION_ID
    | in .env to enable. Leave empty to skip API calls (e.g. local development).
    |
    */

    'api_url' => env('DOCKPLOY_API_URL', 'https://panel.vintorr.com'),
    'token' => env('DOCKPLOY_TOKEN'),
    'application_id' => env('DOCKPLOY_APPLICATION_ID'),

];
