<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

config(['app.debug' => true]);

$request = Illuminate\Http\Request::create('/api/sepay/webhook', 'POST', [], [], [], [
    'HTTP_AUTHORIZATION' => 'Apikey ANlxGJkKFDoB6uy5BEGjfTjsbUEJPOxu6MBvuEjklS4=',
    'HTTP_ACCEPT' => 'application/json',
    'CONTENT_TYPE' => 'application/json'
], json_encode([
    'transferAmount' => 100000,
    'transferType' => 'in',
    'id' => 12345,
    'content' => 'KHODAT TXN123'
]));

$httpKernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $httpKernel->handle($request);
echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
