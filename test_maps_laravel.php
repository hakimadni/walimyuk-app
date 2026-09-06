<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = "https://maps.app.goo.gl/L9HcLK7nkkWyK6aQA";
$response = \Illuminate\Support\Facades\Http::withOptions(['allow_redirects' => true])->head($url);
echo "Final URL: " . $response->effectiveUri() . "\n";
