<?php

$isProduction = config('app.env') === 'production';
$defaultUri = $isProduction ? 'https://lhgroup.service-now.com' : 'https://lhgroupuat.service-now.com';

return [
    'uri' => $isProduction ? env('SNOW_PROD_URI', $defaultUri) : env('SNOW_UAT_URI', $defaultUri),
    'client_id' => $isProduction ? env('SNOW_CLIENT_ID') : env('SNOW_UAT_CLIENT_ID'),
    'client_secret' => $isProduction ? env('SNOW_CLIENT_SECRET') : env('SNOW_UAT_CLIENT_SECRET'),
];
