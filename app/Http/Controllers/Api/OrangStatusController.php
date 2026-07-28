<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use App\Models\Orang;
use App\Models\OrangStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrangStatusController extends Controller
{
    /**
     * Daftar semua status orang.
     */
    public function index(Orang $orang)
    {
        return response()->json(
            $orang->statusList()->with(['jenis', 'peran', 'cabor', 'skala', 'organisasi'])->get()
        );
    }

    /**
     * Tambah 1 status baru ke orang.
     */
    public function store(Request $request, Orang $orang)
    {
        $data = $request->validate([
            'jenis_id'            => 'required|exists:jenis,id',
            'peran_id'            => 'required|exists:peran,id',
            'cabor_id'            => 'nullable|exists:cabors,id',
            'organisasi_id'       => 'nullable|exists:organisasi,id',
            'skala_id'            => 'nullable|exists:skala,id',
            'id_sitenor'          => 'nullable|string|max:100',
            'sertifikat_profesi'  => 'nullable|string|max:255',
            'is_active'           => 'boolean',
        ]);

        $status = $orang->statusList()->create($data);
        LogSistem::catat('CREATE', 'OrangStatus', "Tambah status [{$status->peran?->nama}] untuk {$orang->nama}");

        return response()->json($status->load(['jenis', 'peran', 'cabor', 'skala']), 201);
    }

    /**
     * Simpan banyak status sekaligus (batch upsert dari modal multi-row).
     * Menggantikan seluruh status orang dengan data yang dikirim.
     */
    public function batch(Request $request, Orang $orang)
    {
        $request->validate([
            'items'                       => 'required|array',
            'items.*.jenis_id'            => 'required|exists:jenis,id',
            'items.*.peran_id'            => 'required|exists:peran,id',
            'items.*.cabor_id'            => 'nullable|exists:cabors,id',
            'items.*.organisasi_id'       => 'nullable|exists:organisasi,id',
            'items.*.skala_id'            => 'nullable|exists:skala,id',
            'items.*.id_sitenor'          => 'nullable|string|max:100',
            'items.*.sertifikat_profesi'  => 'nullable|string|max:255',
            'items.*.is_active'           => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            // Hapus semua status lama, ganti dengan yang baru
            $orang->statusList()->delete();

            $created = [];
            foreach ($request->items as $item) {
                $created[] = $orang->statusList()->create(array_merge(
                    $item, ['is_active' => $item['is_active'] ?? true]
                ));
            }

            LogSistem::catat('UPDATE', 'OrangStatus',
                "Batch update " . count($created) . " status untuk {$orang->nama}"
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => count($created) . ' status berhasil disimpan',
            'data'    => collect($created)->load('jenis', 'peran', 'cabor'),
        ]);
    }

    /**
     * Update 1 status.
     */
    public function update(Request $request, Orang $orang, OrangStatus $status)
    {
        $data = $request->validate([
            'jenis_id'            => 'required|exists:jenis,id',
            'peran_id'            => 'required|exists:peran,id',
            'cabor_id'            => 'nullable|exists:cabors,id',
            'organisasi_id'       => 'nullable|exists:organisasi,id',
            'skala_id'            => 'nullable|exists:skala,id',
            'id_sitenor'          => 'nullable|string|max:100',
            'sertifikat_profesi'  => 'nullable|string|max:255',
            'is_active'           => 'boolean',
        ]);

        $status->update($data);
        LogSistem::catat('UPDATE', 'OrangStatus', "Ubah status ID {$status->id} untuk {$orang->nama}");

        return response()->json($status->load(['jenis', 'peran', 'cabor']));
    }

    /**
     * Hapus 1 status.
     */
    public function destroy(Orang $orang, OrangStatus $status)
    {
        LogSistem::catat('DELETE', 'OrangStatus', "Hapus status ID {$status->id} dari {$orang->nama}");
        $status->delete();
        return response()->json(['message' => 'Status berhasil dihapus']);
    }
}
