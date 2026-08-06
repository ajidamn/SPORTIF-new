<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use App\Models\Orang;
use App\Models\RiwayatEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RiwayatEventController extends Controller
{
    /**
     * Riwayat event milik orang tertentu (untuk Tab 3 pada modal orang).
     */
    public function indexByOrang(Orang $orang)
    {
        $riwayat = $orang->riwayatEvent()
            ->with(['event:id,nama,jenis_event,status', 'cabor:id,nama', 'pelatih:id,nama'])
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json($riwayat);
    }

    /**
     * Tambah satu atau lebih riwayat event ke orang tertentu.
     * Validasi ketat: event_id harus ada, orang_id harus ada.
     */
    public function storeForOrang(Request $request, Orang $orang)
    {
        $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.event_id'    => 'required|exists:events,id',
            'items.*.cabor_id'    => 'nullable|exists:cabors,id',
            'items.*.kab_kota_id' => 'nullable|exists:kab_kota,id',
            'items.*.pelatih_id'  => 'nullable|exists:orang,id',
            'items.*.wasit_id'    => 'nullable|exists:orang,id',
            'items.*.kategori'    => 'nullable|string|max:255',
            'items.*.prestasi'    => 'nullable|string|max:255',
            'items.*.medali'      => 'nullable|in:emas,perak,perunggu,-',
            'items.*.tanggal'     => 'nullable|date',
            'items.*.keterangan'  => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $created = [];
            foreach ($request->items as $item) {
                $r = RiwayatEvent::create(array_merge($item, ['orang_id' => $orang->id]));
                $created[] = $r;
            }

            LogSistem::catat('CREATE', 'RiwayatEvent',
                "Menambah " . count($created) . " riwayat event untuk {$orang->nama}"
            );

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => count($created) . ' riwayat berhasil ditambahkan',
            'data'    => $created,
        ], 201);
    }

    /**
     * Update satu riwayat event.
     */
    public function update(Request $request, RiwayatEvent $riwayatEvent)
    {
        $data = $request->validate([
            'event_id'   => 'sometimes|exists:events,id',
            'cabor_id'   => 'nullable|exists:cabors,id',
            'kab_kota_id'=> 'nullable|exists:kab_kota,id',
            'pelatih_id' => 'nullable|exists:orang,id',
            'wasit_id'   => 'nullable|exists:orang,id',
            'kategori'   => 'nullable|string|max:255',
            'prestasi'   => 'nullable|string|max:255',
            'medali'     => 'nullable|in:emas,perak,perunggu,-',
            'tanggal'    => 'nullable|date',
            'keterangan' => 'nullable|string',
        ]);

        $riwayatEvent->update($data);
        LogSistem::catat('UPDATE', 'RiwayatEvent', "Mengubah riwayat event ID {$riwayatEvent->id}");

        return response()->json($riwayatEvent->load(['event', 'cabor', 'pelatih']));
    }

    /**
     * Hapus satu riwayat event.
     */
    public function destroy(RiwayatEvent $riwayatEvent)
    {
        LogSistem::catat('DELETE', 'RiwayatEvent', "Menghapus riwayat event ID {$riwayatEvent->id}");
        $riwayatEvent->delete();
        return response()->json(['message' => 'Riwayat berhasil dihapus']);
    }

    /**
     * Cari orang berdasarkan NIK atau Nama (untuk autocomplete di modal event).
     * Digunakan saat admin input hasil event.
     */
    public function cariOrang(Request $request)
    {
        $q = $request->q;
        if (!$q || strlen($q) < 2) {
            return response()->json([]);
        }

        $orang = Orang::where('nama', 'like', "%{$q}%")
            ->orWhere('nik', 'like', "%{$q}%")
            ->with(['statusList.cabor', 'statusList.peran', 'domisili:id,name'])
            ->limit(10)
            ->get(['id', 'nik', 'nama', 'foto', 'domisili_id']);

        return response()->json($orang);
    }
}
