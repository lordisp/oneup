<?php

$snowClientUri = env('SNOW_CLIENT_URI');
if ($snowClientUri) {
    $snowClientUri = rtrim($snowClientUri, '/');
}

return [
    'uri' => $snowClientUri,
    'client_id' => env('SNOW_CLIENT_ID'),
    'client_secret' => env('SNOW_CLIENT_SECRET'),
];