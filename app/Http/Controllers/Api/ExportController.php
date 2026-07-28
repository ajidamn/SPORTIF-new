<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\DataExport;
use App\Models\LogSistem;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function export(Request $request, string $type)
    {
        $valid = ['orang', 'prasarana', 'sarana', 'events', 'organisasi', 'informasi', 'pengumuman', 'log-sistem', 'users', 'operators', 'sekolah', 'ekstrakurikuler'];

        if (!in_array($type, $valid)) {
            return response()->json(['message' => 'Tipe export tidak valid'], 422);
        }

        LogSistem::catat('EXPORT', ucfirst(str_replace('-', ' ', $type)), "Export data {$type}");

        $filename = "export_{$type}_" . date('Y-m-d_His') . '.xlsx';
        return Excel::download(new DataExport($type, $request->all()), $filename);
    }
}
