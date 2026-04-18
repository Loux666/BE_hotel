<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::create('/api/hotels', 'GET');
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);
if (isset($data['message'])) {
    echo "HTTP " . $response->getStatusCode() . "\n";
    echo "MESSAGE: " . $data['message'] . "\n";
} else {
    echo "STATUS: " . $response->getStatusCode() . "\n";
    echo substr($response->getContent(), 0, 500);
}
