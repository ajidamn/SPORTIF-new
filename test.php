<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\Prasarana;

$prasarana = Prasarana::latest()->first();

$data = [
    'nama' => 'Test Prasarana Updated',
    'lokasi_id' => 1,
    'alamat' => 'Test Alamat',
    'cabor_ids' => [1],
    'fasilitas' => [
        ['id' => $prasarana->fasilitas()->first()->id ?? null, 'nama' => 'Fas 1 Updated', 'jumlah' => 2, 'kondisi' => 'Baik', 'keterangan' => 'Ket']
    ]
];
$request = Request::create('/api/v1/prasarana/' . $prasarana->id, 'PUT', $data);
$request->headers->set('Accept', 'application/json');

$controller = app()->make(\App\Http\Controllers\Api\PrasaranaController::class);
try {
    $response = $controller->update($request, $prasarana);
    echo "Prasarana Update Response: " . $response->getStatusCode() . "\n";
    echo $response->getContent() . "\n";
} catch (\Exception $e) {
    echo "Error Prasarana Update: " . $e->getMessage() . "\n";
}
