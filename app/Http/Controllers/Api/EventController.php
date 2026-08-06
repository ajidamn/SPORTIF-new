<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabor;
use App\Models\Event;
use App\Models\LogSistem;
use App\Models\Orang;
use App\Models\RiwayatEvent;
use App\Models\User;
use App\Models\EventEditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Notifications\EventApprovalNeededNotification;
use App\Notifications\EventApprovedNotification;
use App\Notifications\EventRejectedNotification;
use App\Notifications\EventCreatedNotification;

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

        if ($user && !$user->hasRole('SuperAdmin')) {
            // R9: Hide cancelled
            $query->where(function($q) use ($user) {
                $q->where('status', '!=', 'dibatalkan')
                  ->orWhere('created_by', $user->id);
            });

            // R10: Hide pending
            $canSeeAllPending = $user->hasRole('Admin Dispora Provinsi') 
                             || $user->hasRole('Kepala Dinas Provinsi');
            
            if (!$canSeeAllPending) {
                $query->where(function($q) use ($user) {
                    $q->where('approval_status', 'approved')
                      ->orWhere('created_by', $user->id);
                });
            }
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
            'dokumen_pendukung' => 'nullable|file|max:5120',
            'kapasitas_peserta' => 'nullable|integer|min:1',
        ]);

        $user = auth()->user();
        if ($user && $user->kab_kota_id) {
            $data['kab_kota_id'] = $user->kab_kota_id;
        }

        // Duplicate prevention
        $namaUpper = mb_strtoupper(trim($data['nama']));
        $duplicate = Event::whereRaw('UPPER(nama) = ?', [$namaUpper])
            ->where('jenis_id', $data['jenis_id'])
            ->where('tahun', $data['tahun'] ?? null)
            ->where('kab_kota_id', $data['kab_kota_id'] ?? null)
            ->first();

        if ($duplicate) {
            return response()->json([
                'message' => "Event dengan nama \"{$data['nama']}\" pada tahun yang sama dan domain yang sama sudah ada.",
                'existing_event' => $duplicate->only('id', 'nama', 'tahun', 'status')
            ], 422);
        }

        $data['created_by'] = $user ? $user->id : null;

        if ($user && $user->kab_kota_id && isset($data['skala_id'])) {
            $skala = \App\Models\Skala::find($data['skala_id']);
            if ($skala && $skala->nama !== 'Daerah') {
                $data['approval_status'] = 'pending';
            }
        }

        if ($request->hasFile('dokumen_pendukung')) {
            $data['dokumen_pendukung'] = $request->file('dokumen_pendukung')->store('events/dokumen', 'public');
        }

        DB::beginTransaction();
        try {
            $event = Event::create($data);

            if (!empty($data['cabor_ids'])) {
                $event->cabors()->sync($data['cabor_ids']);
            }

            EventEditLog::create([
                'event_id'   => $event->id,
                'user_id'    => auth()->id(),
                'action'     => 'create',
                'changes'    => json_encode($event->toArray()),
                'ip_address' => $request->ip(),
            ]);

            if ($event->approval_status === 'pending') {
                $this->notifyApprovalNeeded($event);
            } else {
                $this->notifyEventCreated($event);
            }

            LogSistem::catat('CREATE', 'Event', "Menambah event: {$event->nama}", null, $event->toArray());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        return response()->json($event->load(['jenis', 'skala', 'cabors', 'creator']), 201);
    }

    public function show(Event $event)
    {
        return response()->json($event->load(['jenis', 'skala', 'cabors', 'riwayat.orang', 'riwayat.cabor', 'riwayat.pelatih']));
    }

    public function update(Request $request, Event $event)
    {
        if (!$event->isEditableBy(auth()->user())) {
            return response()->json(['message' => 'Unauthorized. Hanya pembuat event atau SuperAdmin yang dapat mengedit.'], 403);
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
            'dokumen_pendukung' => 'nullable|file|max:5120',
            'kapasitas_peserta' => 'nullable|integer|min:1',
        ]);

        $user = auth()->user();
        if ($user && $user->kab_kota_id) {
            $data['kab_kota_id'] = $user->kab_kota_id;
        }

        // Duplicate Check (exclude self)
        $namaUpper = mb_strtoupper(trim($data['nama']));
        $duplicate = Event::whereRaw('UPPER(nama) = ?', [$namaUpper])
            ->where('jenis_id', $data['jenis_id'])
            ->where('tahun', $data['tahun'] ?? null)
            ->where('kab_kota_id', $data['kab_kota_id'] ?? null)
            ->where('id', '!=', $event->id)
            ->first();

        if ($duplicate) {
            return response()->json([
                'message' => "Event dengan nama \"{$data['nama']}\" sudah ada.",
                'existing_event' => $duplicate->only('id', 'nama', 'tahun', 'status'),
            ], 422);
        }

        if ($request->hasFile('dokumen_pendukung')) {
            $data['dokumen_pendukung'] = $request->file('dokumen_pendukung')->store('events/dokumen', 'public');
        }

        $old = $event->toArray();

        DB::beginTransaction();
        try {
            $event->update($data);
            $event->cabors()->sync($data['cabor_ids'] ?? []);

            EventEditLog::create([
                'event_id'   => $event->id,
                'user_id'    => auth()->id(),
                'action'     => 'update',
                'changes'    => json_encode(['old' => $old, 'new' => $event->fresh()->toArray()]),
                'ip_address' => $request->ip(),
            ]);

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
        if (!$event->isEditableBy(auth()->user())) {
            return response()->json(['message' => 'Unauthorized. Hanya pembuat atau SuperAdmin yang dapat menghapus.'], 403);
        }

        EventEditLog::create([
            'event_id'   => $event->id,
            'user_id'    => auth()->id(),
            'action'     => 'delete',
            'changes'    => json_encode($event->toArray()),
            'ip_address' => request()->ip(),
        ]);

        $nama = $event->nama;
        LogSistem::catat('DELETE', 'Event', "Menghapus event: {$nama}", $event->toArray());
        $event->delete();
        return response()->json(['message' => 'Event berhasil dihapus']);
    }

    public function riwayat(Event $event)
    {
        $riwayat = $event->riwayat()
            ->with(['orang:id,nama,nik', 'cabor:id,nama', 'pelatih:id,nama', 'wasit:id,nama', 'kab_kota:id,name'])
            ->get();
        return response()->json($riwayat);
    }

    // ── Import Riwayat dari Excel ─────────────────────────────

    /**
     * Import hasil/riwayat event dari file Excel.
     * Kolom yang dibutuhkan: NIK, Nama, Cabor, Kategori, Prestasi, Medali, Tanggal, Keterangan
     * Validasi: NIK/Nama WAJIB ditemukan di tabel orang — jika tidak → skip + catat error.
     */
    public function downloadTemplateRiwayat(Event $event)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($event) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                private $event;
                public function __construct($event) { $this->event = $event; }
                
                public function sheets(): array {
                    $headers = ['NIK', 'NAMA', 'CABOR', 'KATEGORI_PERTANDINGAN', 'KONTINGEN_KAB_KOTA', 'PRESTASI', 'MEDALI', 'TANGGAL_MEDALI', 'KETERANGAN'];
                    
                    $sheet1 = new class($headers) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\WithTitle {
                        private array $h;
                        public function __construct(array $h) { $this->h = $h; }
                        public function headings(): array { return $this->h; }
                        public function array(): array { return []; }
                        public function title(): string { return 'Template Input'; }
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s) {
                            return [1 => [
                                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A56DB']],
                            ]];
                        }
                    };

                    $sheet2 = new class($this->event) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles {
                        private $event;
                        public function __construct($event) { $this->event = $event; }
                        public function title(): string { return 'Panduan & Referensi Data'; }
                        public function array(): array {
                            $data = [
                                ['PANDUAN PENGISIAN EXCEL', ''],
                                ['1. NIK / NAMA', 'Wajib diisi. NIK (16 digit) lebih diprioritaskan. Peserta harus sudah ada di master data Orang.'],
                                ['2. CABOR', 'Wajib disi dengan salah satu cabang olahraga di event ini. (Lihat daftar Cabor di bawah)'],
                                ['3. KATEGORI_PERTANDINGAN', 'Bebas (Opsional). Contoh: -60kg Putra, Ganda Campuran.'],
                                ['4. KONTINGEN_KAB_KOTA', 'Opsional. Nama kontingen harus sesuai database. (Lihat daftar Kab/Kota di bawah)'],
                                ['5. PRESTASI', 'Bebas (Opsional). Contoh: Juara 1, Runner Up.'],
                                ['6. MEDALI', 'Opsional. Hanya menerima nilai: emas, perak, perunggu, atau - (strip).'],
                                ['7. TANGGAL_MEDALI', 'Opsional. Format penulisan tanggal Excel biasa (cth: YYYY-MM-DD).'],
                                ['8. KETERANGAN', 'Bebas (Opsional).'],
                                ['', ''],
                                ['REFERENSI CABOR (Khusus Event Ini)', ''],
                            ];
                            
                            foreach($this->event->cabors as $c) {
                                $data[] = ['- ' . $c->nama, ''];
                            }
                            
                            $data[] = ['', ''];
                            $data[] = ['REFERENSI KONTINGEN KAB/KOTA', ''];
                            
                            $kabKotas = \App\Models\KabKota::orderBy('name')->get();
                            foreach($kabKotas as $k) {
                                $data[] = ['- ' . $k->name, ''];
                            }
                            
                            return $data;
                        }
                        public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s) {
                            $s->getColumnDimension('A')->setWidth(35);
                            $s->getColumnDimension('B')->setWidth(80);
                            
                            // Hitung posisi judul referensi cabor
                            $caborRow = 11;
                            
                            return [
                                1 => ['font' => ['bold' => true, 'size' => 12]],
                                $caborRow => ['font' => ['bold' => true, 'size' => 12]],
                            ];
                        }
                    };

                    return [$sheet1, $sheet2];
                }
            },
            "template_peserta_{$event->id}.xlsx"
        );
    }

    public function exportRiwayat(Event $event)
    {
        $riwayat = $event->riwayat()
            ->with(['orang:id,nama,nik', 'cabor:id,nama', 'kab_kota:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $data = $riwayat->map(function ($r, $idx) {
            return [
                'No' => $idx + 1,
                'NIK' => $r->orang?->nik,
                'Nama' => $r->orang?->nama,
                'Cabor' => $r->cabor?->nama,
                'Kategori' => $r->kategori,
                'Kontingen' => $r->kab_kota?->name,
                'Prestasi' => $r->prestasi,
                'Medali' => $r->medali,
                'Tanggal' => $r->tanggal ? $r->tanggal->format('Y-m-d') : '',
                'Keterangan' => $r->keterangan,
            ];
        })->toArray();

        $headers = ['NO', 'NIK', 'NAMA', 'CABOR', 'KATEGORI_PERTANDINGAN', 'KONTINGEN_KAB_KOTA', 'PRESTASI', 'MEDALI', 'TANGGAL_MEDALI', 'KETERANGAN'];

        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($data, $headers) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles {
                private array $d;
                private array $h;
                public function __construct(array $d, array $h) { $this->d = $d; $this->h = $h; }
                public function headings(): array { return $this->h; }
                public function array(): array { return $this->d; }
                public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $s) {
                    return [1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '198754']],
                    ]];
                }
            },
            "peserta_event_{$event->id}.xlsx"
        );
    }

    public function importRiwayat(Request $request, Event $event)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $rows = \Maatwebsite\Excel\Facades\Excel::toArray([], $request->file('file'));
        if (empty($rows[0]) || count($rows[0]) < 2) {
            return response()->json(['message' => 'File kosong atau format tidak sesuai (minimal ada header dan 1 baris data)'], 422);
        }

        $headers = array_map('strtolower', array_map('trim', $rows[0][0]));
        $dataRows = array_slice($rows[0], 1);

        $berhasil = 0;
        $gagal    = [];
        $kabKotaCache = \App\Models\KabKota::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();

        foreach ($dataRows as $idx => $row) {
            $line = $idx + 2;
            $map  = array_combine($headers, array_pad($row, count($headers), null));

            $nik  = trim((string)($map['nik'] ?? ''));
            $nama = trim((string)($map['nama'] ?? ''));

            $orang = null;
            if ($nik) $orang = Orang::where('nik', $nik)->first();
            if (!$orang && $nama) $orang = Orang::where('nama', $nama)->first();

            if (!$orang) {
                $gagal[] = [
                    'baris'  => $line,
                    'data'   => ['nik' => $nik, 'nama' => $nama],
                    'alasan' => 'Orang tidak ditemukan di sistem (NIK/Nama belum terdaftar)',
                ];
                continue;
            }

            // Cari Kontingen Kab/Kota
            $kontingenNama = trim((string)($map['kontingen_kab_kota'] ?? ''));
            $kabKotaId = null;
            if ($kontingenNama) {
                $kabKotaId = $kabKotaCache[strtolower($kontingenNama)] ?? null;
                if (!$kabKotaId) {
                    $gagal[] = [
                        'baris'  => $line,
                        'data'   => ['nama' => $nama, 'kontingen' => $kontingenNama],
                        'alasan' => "Kontingen Kab/Kota '{$kontingenNama}' tidak valid/tidak ditemukan",
                    ];
                    continue;
                }
            }

            $caborNama = trim((string)($map['cabor'] ?? ''));
            $cabor = $caborNama ? Cabor::where('nama', 'like', "%{$caborNama}%")->first() : null;

            $medali = strtolower(trim((string)($map['medali'] ?? '')));
            if (!in_array($medali, ['emas', 'perak', 'perunggu', '-'])) {
                $medali = null;
            }
            
            $tanggal = trim((string)($map['tanggal_medali'] ?? ''));
            if (is_numeric($tanggal)) {
                try { $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$tanggal)->format('Y-m-d'); }
                catch (\Throwable) { $tanggal = null; }
            } else if ($tanggal) {
                try { $tanggal = date('Y-m-d', strtotime($tanggal)); }
                catch (\Throwable) { $tanggal = null; }
            }

            RiwayatEvent::create([
                'event_id'  => $event->id,
                'orang_id'  => $orang->id,
                'cabor_id'  => $cabor?->id,
                'kab_kota_id' => $kabKotaId,
                'kategori'  => trim((string)($map['kategori_pertandingan'] ?? '')) ?: null,
                'prestasi'  => trim((string)($map['prestasi'] ?? '')) ?: null,
                'medali'    => $medali,
                'tanggal'   => $tanggal,
                'keterangan'=> trim((string)($map['keterangan'] ?? '')) ?: null,
            ]);

            $berhasil++;
        }

        LogSistem::catat('IMPORT', 'RiwayatEvent', "Import riwayat event '{$event->nama}': {$berhasil} berhasil, " . count($gagal) . " gagal");

        return response()->json([
            'total'    => count($dataRows),
            'berhasil' => $berhasil,
            'gagal'    => count($gagal),
            'detail_gagal' => array_slice($gagal, 0, 100),
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

    // ── Approval & Audit ─────────────────────────────────────

    public function approve(Event $event)
    {
        $user = auth()->user();
        if (!$user->hasRole('SuperAdmin') && !$user->hasRole('Admin Dispora Provinsi')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $old = $event->toArray();
        $event->update(['approval_status' => 'approved']);

        EventEditLog::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'action' => 'approve',
            'changes' => json_encode(['old' => $old, 'new' => $event->fresh()->toArray()]),
            'ip_address' => request()->ip(),
        ]);
        LogSistem::catat('APPROVE', 'Event', "Approve event: {$event->nama}");

        $this->notifyEventApproved($event);

        return response()->json(['message' => 'Event berhasil disetujui', 'event' => $event->fresh()]);
    }

    public function reject(Request $request, Event $event)
    {
        $user = auth()->user();
        if (!$user->hasRole('SuperAdmin') && !$user->hasRole('Admin Dispora Provinsi')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['alasan' => 'required|string|max:500']);
        $event->update(['approval_status' => 'rejected']);

        EventEditLog::create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'action' => 'reject',
            'changes' => json_encode(['alasan' => $request->alasan]),
            'ip_address' => request()->ip(),
        ]);
        LogSistem::catat('REJECT', 'Event', "Reject event: {$event->nama}. Alasan: {$request->alasan}");

        $this->notifyEventRejected($event, $request->alasan);

        return response()->json(['message' => 'Event ditolak']);
    }

    public function editLogs(Event $event)
    {
        return response()->json($event->editLogs()->with('user:id,name')->latest()->get());
    }

    // ── Notifikasi Helper ────────────────────────────────────

    private function notifyApprovalNeeded(Event $event)
    {
        $users = User::whereHas('roles', function($q) {
            $q->where('name', 'Admin Dispora Provinsi');
        })->get();
        Notification::send($users, new EventApprovalNeededNotification($event));
    }

    private function notifyEventCreated(Event $event)
    {
        $users = User::where('id', '!=', auth()->id())
            ->where('is_active', true)
            ->where(function($q) use ($event) {
                $q->where('jenis_id', $event->jenis_id)
                  ->orWhereNull('jenis_id');
            })
            ->where(function($q) use ($event) {
                if ($event->skala && $event->skala->nama === 'Daerah') {
                    $q->where('kab_kota_id', $event->kab_kota_id)
                      ->orWhereNull('kab_kota_id');
                }
            })
            ->get()
            ->filter(function($user) use ($event) {
                if ($user->cabor_id) {
                    return $event->cabors->pluck('id')->contains($user->cabor_id);
                }
                return true;
            });

        Notification::send($users, new EventCreatedNotification($event));
    }

    private function notifyEventApproved(Event $event)
    {
        $users = collect([$event->creator])->filter();
        Notification::send($users, new EventApprovedNotification($event));
        
        // Also notify users who can see it
        $this->notifyEventCreated($event);
    }

    private function notifyEventRejected(Event $event, $alasan)
    {
        if ($event->creator) {
            $event->creator->notify(new EventRejectedNotification($event, $alasan));
        }
    }
}
