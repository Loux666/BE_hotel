<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$id = 1; // Default test ID
$request = Illuminate\Http\Request::create("/api/hotels/$id", 'GET');
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
$data = json_decode($response->getContent(), true);

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
