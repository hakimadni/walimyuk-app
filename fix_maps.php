<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$events = \App\Models\Event::whereNotNull('google_maps_url')->whereNull('latitude')->get();
foreach ($events as $event) {
    $url = $event->google_maps_url;
    echo "Processing $url\n";
    try {
        $response = \Illuminate\Support\Facades\Http::withOptions(['allow_redirects' => true])->head($url);
        $finalUrl = $response->effectiveUri() ? (string) $response->effectiveUri() : $url;
        
        $lat = null;
        $lng = null;
        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $finalUrl, $matches)) {
            $lat = (float) $matches[1];
            $lng = (float) $matches[2];
        } elseif (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $finalUrl, $matches)) {
            $lat = (float) $matches[1];
            $lng = (float) $matches[2];
        }
        
        if ($lat && $lng) {
            $event->update(['latitude' => $lat, 'longitude' => $lng]);
            echo "Updated {$event->id} with $lat, $lng\n";
        }
    } catch (\Throwable $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
