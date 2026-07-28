<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabor;
use App\Models\Event;
use App\Models\LogSistem;
use App\Models\Orang;
use App\Models\RiwayatEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class EventController extends Controller
{
    // ── CRUD Event ───────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Event::with(['jenis', 'skala', 'cabors']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('penyelenggara', 'like', "%{$request->search}%");
            });
        }
        if ($request->jenis_id)     $query->where('jenis_id', $request->jenis_id);
        if ($request->skala_id)     $query->where('skala_id', $request->skala_id);
        if ($request->jenis_event)  $query->where('jenis_event', $request->jenis_event);
        if ($request->status)       $query->where('status', $request->status);
        if ($request->tahun)        $query->where('tahun', $request->tahun);
        if ($request->has('disabilitas')) $query->where('disabilitas', in_array($request->disabilitas, ['1', 1, true, 'true'], true));

        $user = auth()->user();

        if ($request->kab_kota_id) {
            // Jika user adalah Kab/Kota Admin, jangan gunakan filter kab_kota_id 
            // agar data provinsi/nasional (tenancy) tetap muncul
            if (!($user && $user->kab_kota_id && $user->kab_kota_id == $request->kab_kota_id)) {
                $query->where('kab_kota_id', $request->kab_kota_id);
            }
        }

        if ($user && $user->kab_kota_id) {
            $query->where(function($q) use ($user) {
                $q->where('kab_kota_id', $user->kab_kota_id)
                  ->orWhereHas('skala', function($sq) {
                      $sq->where('nama', '!=', 'Daerah');
                  });
            });
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kab_kota_id'     => 'nullable|exists:kab_kota,id',
            'jenis_id'        => 'required|exists:jenis,id',
            'nama'            => 'required|string|max:255',
            'tahun'           => 'nullable|integer|min:2000|max:2099',
            'skala_id'        => 'nullable|exists:skala,id',
            'jenis_event'     => 'required|in:single event,multi event,pelatihan,perlombaan',
            'penyelenggara'   => 'required|string|max:255',
            'lokasi_kegiatan' => 'nullable|string|max:255',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'in:aktif,selesai,dibatalkan',
            'disabilitas'     => 'nullable|boolean',
            'cabor_ids'       => 'nullable|array',
            'cabor_ids.*'     => 'exists:cabors,id',
        ]);

        $user = auth()->user();
        if ($user && $user->kab_kota_id) {
            $data['kab_kota_id'] = $user->kab_kota_id;
        }

        DB::beginTransaction();
        try {
            $event = Event::create($data);

            if (!empty($data['cabor_ids'])) {
                $event->cabors()->sync($data['cabor_ids']);
            }

            LogSistem::catat('CREATE', 'Event', "Menambah event: {$event->nama}", null, $event->toArray());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        return response()->json($event->load(['jenis', 'skala', 'cabors']), 201);
    }

    public function show(Event $event)
    {
        return response()->json($event->load(['jenis', 'skala', 'cabors', 'riwayat.orang', 'riwayat.cabor', 'riwayat.pelatih']));
    }

    public function update(Request $request, Event $event)
    {
        $old = $event->toArray();

        $user = auth()->user();
        if ($user && $user->kab_kota_id && $event->kab_kota_id !== $user->kab_kota_id) {
            return response()->json(['message' => 'Unauthorized. Hanya bisa mengedit event dari Kab/Kota Anda sendiri.'], 403);
        }

        $data = $request->validate([
            'kab_kota_id'     => 'nullable|exists:kab_kota,id',
            'jenis_id'        => 'required|exists:jenis,id',
            'nama'            => 'required|string|max:255',
            'tahun'           => 'nullable|integer|min:2000|max:2099',
            'skala_id'        => 'nullable|exists:skala,id',
            'jenis_event'     => 'required|in:single event,multi event,pelatihan,perlombaan',
            'penyelenggara'   => 'required|string|max:255',
            'lokasi_kegiatan' => 'nullable|string|max:255',
            'tanggal_mulai'   => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'status'          => 'in:aktif,selesai,dibatalkan',
            'disabilitas'     => 'nullable|boolean',
            'cabor_ids'       => 'nullable|array',
            'cabor_ids.*'     => 'exists:cabors,id',
        ]);

        $user = auth()->user();
        if ($user && $user->kab_kota_id) {
            $data['kab_kota_id'] = $user->kab_kota_id;
        }

        DB::beginTransaction();
        try {
            $event->update($data);
            $event->cabors()->sync($data['cabor_ids'] ?? []);
            LogSistem::catat('UPDATE', 'Event', "Mengubah event: {$event->nama}", $old, $event->toArray());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal mengubah: ' . $e->getMessage()], 500);
        }

        return response()->json($event->load(['jenis', 'skala', 'cabors']));
    }


    // ── Cabor filter by jenis_id ─────────────────────────────

    /**
     * Daftar cabor yang sesuai dengan jenis event.
     * jenis_id=1 → olahraga_prestasi, jenis_id=2 → olahraga_masyarakat
     */
    public function caborByJenis(int $jenisId)
    {
        $tipe = match($jenisId) {
            1 => 'olahraga_prestasi',
            2 => 'olahraga_masyarakat',
            default => null,
        };

        $query = Cabor::orderBy('nama');
        if ($tipe) $query->where('tipe', $tipe);

        return response()->json($query->get(['id', 'nama', 'tipe']));
    }

    // ── Riwayat per Event ────────────────────────────────────

    public function destroy(Event $event)
    {
        $user = auth()->user();
        if ($user && $user->kab_kota_id && $event->kab_kota_id !== $user->kab_kota_id) {
            return response()->json(['message' => 'Unauthorized. Hanya bisa menghapus event dari Kab/Kota Anda sendiri.'], 403);
        }

        $nama = $event->nama;
        LogSistem::catat('DELETE', 'Event', "Menghapus event: {$nama}", $event->toArray());
        $event->delete();
        return response()->json(['message' => 'Event berhasil dihapus']);
    }

    public function riwayat(Event $event)
    {
        $riwayat = $event->riwayat()
            ->with(['orang:id,nama,nik', 'cabor:id,nama', 'pelatih:id,nama', 'wasit:id,nama'])
            ->get();
        return response()->json($riwayat);
    }

    // ── Import Riwayat dari Excel ─────────────────────────────

    /**
     * Import hasil/riwayat event dari file Excel.
     * Kolom yang dibutuhkan: NIK, Nama, Cabor, Kategori, Prestasi, Medali, Tanggal, Keterangan
     * Validasi: NIK/Nama WAJIB ditemukan di tabel orang — jika tidak → skip + catat error.
     */
    public function importRiwayat(Request $request, Event $event)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $rows = Excel::toArray([], $request->file('file'));
        if (empty($rows[0])) {
            return response()->json(['message' => 'File kosong atau format tidak sesuai'], 422);
        }

        $headers = array_map('strtolower', array_map('trim', $rows[0][0]));
        $dataRows = array_slice($rows[0], 1);

        $berhasil = 0;
        $gagal    = [];

        foreach ($dataRows as $idx => $row) {
            $line = $idx + 2; // nomor baris di Excel (mulai dari 2)
            $map  = array_combine($headers, $row);

            $nik  = trim($map['nik'] ?? '');
            $nama = trim($map['nama'] ?? '');

            // Cari orang berdasarkan NIK atau Nama
            $orang = null;
            if ($nik) {
                $orang = Orang::where('nik', $nik)->first();
            }
            if (!$orang && $nama) {
                $orang = Orang::where('nama', $nama)->first();
            }

            if (!$orang) {
                $gagal[] = [
                    'baris'  => $line,
                    'data'   => ['nik' => $nik, 'nama' => $nama],
                    'alasan' => 'Orang tidak ditemukan di database (NIK/Nama tidak cocok)',
                ];
                continue;
            }

            // Cari cabor
            $caborNama = trim($map['cabor'] ?? '');
            $cabor = Cabor::where('nama', 'like', "%{$caborNama}%")->first();

            // Validasi medali
            $medali = strtolower(trim($map['medali'] ?? ''));
            if (!in_array($medali, ['emas', 'perak', 'perunggu', '-'])) {
                $medali = null;
            }

            RiwayatEvent::create([
                'event_id'  => $event->id,
                'orang_id'  => $orang->id,
                'cabor_id'  => $cabor?->id,
                'kategori'  => $map['kategori'] ?? null,
                'prestasi'  => $map['prestasi'] ?? null,
                'medali'    => $medali,
                'tanggal'   => !empty($map['tanggal']) ? date('Y-m-d', strtotime($map['tanggal'])) : null,
                'keterangan'=> $map['keterangan'] ?? null,
            ]);

            $berhasil++;
        }

        LogSistem::catat('IMPORT', 'RiwayatEvent', "Import riwayat event '{$event->nama}': {$berhasil} berhasil, " . count($gagal) . " gagal");

        return response()->json([
            'berhasil' => $berhasil,
            'gagal'    => count($gagal),
            'detail_gagal' => $gagal,
        ]);
    }

    // ── Public Event Index (untuk kalender) ──────────────────

    /**
     * Public listing event — support filter bulan/tahun untuk kalender.
     * ISO 27001: Hanya return field yang dibutuhkan publik.
     */
    public function publicIndex(Request $request)
    {
        $request->validate([
            'month'      => 'nullable|integer|min:1|max:12',
            'year'       => 'nullable|integer|min:2020|max:2099',
            'jenis_id'   => 'nullable|integer|exists:jenis,id',
            'status'     => 'nullable|in:aktif,selesai,dibatalkan',
            'per_page'   => 'nullable|integer|min:1|max:100',
        ]);

        $query = Event::with(['jenis', 'skala', 'cabors:id,nama'])
            ->select('id', 'jenis_id', 'nama', 'tahun', 'skala_id', 'jenis_event',
                     'penyelenggara', 'lokasi_kegiatan',
                     'tanggal_mulai', 'tanggal_selesai', 'status');

        if ($request->month && $request->year) {
            $query->where(function ($q) use ($request) {
                $q->whereMonth('tanggal_mulai', $request->month)
                  ->whereYear('tanggal_mulai', $request->year);
            })->orWhere(function ($q) use ($request) {
                $q->whereMonth('tanggal_selesai', $request->month)
                  ->whereYear('tanggal_selesai', $request->year);
            });
        } elseif ($request->year) {
            $query->whereYear('tanggal_mulai', $request->year);
        }

        if ($request->jenis_id) {
            $query->where('jenis_id', $request->jenis_id);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        return response()->json(
            $query->orderBy('tanggal_mulai', 'desc')
                  ->paginate($request->per_page ?? 50)
        );
    }
}
