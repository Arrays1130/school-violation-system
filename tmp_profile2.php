<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$start = microtime(true);
$request = Illuminate\Http\Request::create('/login', 'GET');
$response = $kernel->handle($request);
$end = microtime(true);
echo "Profile for /login GET\n";
echo "Total Time: " . round($end - $start, 4) . " seconds\n";
echo "Status Code: " . $response->getStatusCode() . "\n";
echo "===============================\n";

$start = microtime(true);
$request = Illuminate\Http\Request::create('/dashboard', 'GET');
// Mock Auth
$user = \App\Models\User::first();
auth()->login($user);
$response = $kernel->handle($request);
$end = microtime(true);
echo "Profile for /dashboard GET\n";
echo "Total Time: " . round($end - $start, 4) . " seconds\n";
echo "Status Code: " . $response->getStatusCode() . "\n";