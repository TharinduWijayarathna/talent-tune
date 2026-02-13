<?php

return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'price_id' => env('STRIPE_PRICE_ID'),
    'currency' => env('STRIPE_CURRENCY', 'usd'),
];
