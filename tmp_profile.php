<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$start = microtime(true);

$request = Illuminate\Http\Request::create('/dashboard', 'GET');
$response = $kernel->handle($request);

$end = microtime(true);
$time = $end - $start;

echo "\n===============================\n";
echo "Profile for /dashboard GET\n";
echo "Total Time: " . round($time, 4) . " seconds\n";
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "===============================\n";

$kernel->terminate($request, $response);
