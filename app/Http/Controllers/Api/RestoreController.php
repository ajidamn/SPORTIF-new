<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use Illuminate\Http\Request;

class RestoreController extends Controller
{
    protected $models = [
        'orang' => \App\Models\Orang::class,
        'prasarana' => \App\Models\Prasarana::class,
        'sarana' => \App\Models\Sarana::class,
        'events' => \App\Models\Event::class,
        'organisasi' => \App\Models\Organisasi::class,
        'sekolah' => \App\Models\Sekolah::class,
        'ekstrakurikuler' => \App\Models\EkstrakurikulerSekolah::class,
        'cabor' => \App\Models\Cabor::class,
        'users' => \App\Models\User::class,
        'operators' => \App\Models\Operator::class,
    ];

    public function restore(Request $request, $model, $id)
    {
        if (!array_key_exists($model, $this->models)) {
            return response()->json(['message' => 'Model tidak didukung untuk restore'], 400);
        }

        $modelClass = $this->models[$model];
        $data = $modelClass::withTrashed()->findOrFail($id);
        
        $data->restore();

        // Ambil nama atau atribut untuk log
        $nama = $data->nama ?? $data->nama_sekolah ?? $data->nama_barang ?? 'Data';

        LogSistem::catat('RESTORE', class_basename($modelClass), "Mengembalikan data yang dihapus: {$nama}", null, $data->toArray());

        return response()->json(['message' => 'Data berhasil dikembalikan']);
    }
}
