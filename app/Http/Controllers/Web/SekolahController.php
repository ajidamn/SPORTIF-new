<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Sekolah;

class SekolahController extends Controller
{
    public function show(Sekolah $sekolah)
    {
        // Pastikan hanya admin dengan hak akses memadai atau sesuai region
        $user = auth()->user();
        if ($user->kab_kota_id && !$user->hasRole('SuperAdmin') && $user->kab_kota_id != $sekolah->kab_kota_id) {
            abort(403, 'Unauthorized access to this school.');
        }

        $sekolah->load(['kabKota', 'creator']);

        return view('admin.sekolah.show', compact('sekolah'));
    }
}
