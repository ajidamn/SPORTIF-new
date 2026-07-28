<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Orang, OrangStatus, Prasarana, Sarana, Event, Organisasi, Cabor, Jenis, KabKota, Peran, Skala, LogSistem, Sekolah, EkstrakurikulerSekolah, JenisEkstrakurikuler};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\{FromArray, WithHeadings, WithStyles};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ImportController extends Controller
{
    private const BATCH_SIZE = 500;
    private const MAX_ROWS   = 5000;

    private const TEMPLATES = [
        'orang' => [
            'headers' => ['NIK','NAMA','TGL_LAHIR','GENDER','TELP','ALAMAT','DOMISILI','DISABILITAS','JENIS_DISABILITAS','TINGGI','BERAT','GOL_DARAH','JENIS','PERAN','CABOR','ORGANISASI','SKALA'],
        ],
        'prasarana' => [
            'headers' => ['NAMA','JENIS','LOKASI','PENGELOLA','NARAHUBUNG','TELP_NARAHUBUNG','ALAMAT','KAPASITAS','KETERANGAN'],
        ],
        'events' => [
            'headers' => ['NAMA','JENIS','SKALA','JENIS_EVENT','PENYELENGGARA','LOKASI_KEGIATAN','TANGGAL_MULAI','TANGGAL_SELESAI','STATUS'],
        ],
        'organisasi' => [
            'headers' => ['NAMA','JENIS','ALAMAT','TELP','EMAIL','NARAHUBUNG','SK_PENDIRIAN','TGL_SK_PENDIRIAN','KAB_KOTA','STATUS','SKALA'],
        ],
        'sarana' => [
            'headers' => ['NAMA_BARANG','KODE_INVENTARIS','JENIS','CABOR','KONDISI','STATUS','POSISI_ASET','LOKASI_BARANG','KETERANGAN_LOKASI','JUMLAH','SATUAN','TAHUN_PENGADAAN','SUMBER_DANA','KAB_KOTA'],
        ],
        'master-jenis-ekstrakurikuler' => [
            'headers' => ['NAMA','KATEGORI','CABOR_TERKAIT','KETERANGAN','STATUS_AKTIF'],
        ],
        'sekolah' => [
            'headers' => ['NAMA_SEKOLAH','JENIS_SEKOLAH','STATUS_SEKOLAH','KAB_KOTA','NARAHUBUNG','TELEPON'],
        ],
        'ekstrakurikuler' => [
            'headers' => ['NAMA_SEKOLAH','JENIS_EKSKUL','NAMA_PEMBINA','JUMLAH_ANGGOTA_PUTRA','JUMLAH_ANGGOTA_PUTRI','JADWAL_PERTEMUAN','STATUS','NARAHUBUNG','TELEPON'],
        ],
        'operators' => [
            'headers' => ['NIK','NAMA','NIP','JABATAN','ROLE','SKALA','KAB_KOTA','CABOR','EMAIL','NO_TELP'],
        ],
    ];

    // ── Download Template ──────────────────────────────────

    public function downloadTemplate(string $type)
    {
        if (!isset(self::TEMPLATES[$type])) {
            return response()->json(['message' => 'Tipe tidak valid'], 422);
        }

        $headers = self::TEMPLATES[$type]['headers'];

        return Excel::download(
            new class($headers) implements FromArray, WithHeadings, WithStyles {
                private array $h;
                public function __construct(array $h) { $this->h = $h; }
                public function headings(): array { return $this->h; }
                public function array(): array { return []; }
                public function styles(Worksheet $s) {
                    return [1 => [
                        'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A56DB']],
                    ]];
                }
            },
            "template_{$type}.xlsx"
        );
    }

    // ── Import Data ────────────────────────────────────────

    public function import(Request $request, string $type)
    {
        if (!isset(self::TEMPLATES[$type])) {
            return response()->json(['message' => 'Tipe import tidak valid'], 422);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $rows = Excel::toArray([], $request->file('file'));
        } catch (\Throwable $e) {
            return response()->json(['message' => 'File tidak dapat dibaca: ' . $e->getMessage()], 422);
        }

        if (empty($rows[0]) || count($rows[0]) < 2) {
            return response()->json(['message' => 'File kosong atau hanya berisi header'], 422);
        }

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(str_replace(' ', '_', trim($h ?? ''))), $rows[0][0]);
        $dataRows = array_slice($rows[0], 1);

        // Filter empty rows
        $dataRows = array_values(array_filter($dataRows, fn($row) =>
            !empty(array_filter($row, fn($c) => $c !== null && trim((string)$c) !== ''))
        ));

        $totalRows = count($dataRows);

        if ($totalRows === 0) {
            return response()->json(['message' => 'Tidak ada data untuk diproses'], 422);
        }
        if ($totalRows > self::MAX_ROWS) {
            return response()->json(['message' => "File berisi {$totalRows} baris. Maksimal " . self::MAX_ROWS . " baris per import."], 422);
        }

        // Pre-cache lookup tables
        $lookups = $this->buildLookups();

        $berhasil = 0;
        $gagal    = [];
        $batches  = array_chunk($dataRows, self::BATCH_SIZE, true);

        foreach ($batches as $batchIdx => $batch) {
            DB::beginTransaction();
            try {
                foreach ($batch as $rowIdx => $row) {
                    $line = $rowIdx + 2; // Excel baris (header = 1)
                    while (count($row) < count($headers)) $row[] = null;
                    $map = @array_combine($headers, $row);
                    if (!$map) {
                        $gagal[] = ['baris' => $line, 'data' => [], 'alasan' => 'Jumlah kolom tidak sesuai header'];
                        continue;
                    }

                    $result = match ($type) {
                        'orang'                        => $this->importOrang($map, $line, $lookups),
                        'prasarana'                    => $this->importPrasarana($map, $line, $lookups),
                        'events'                       => $this->importEvent($map, $line, $lookups),
                        'organisasi'                   => $this->importOrganisasi($map, $line, $lookups),
                        'sarana'                       => $this->importSarana($map, $line, $lookups),
                        'master-jenis-ekstrakurikuler' => $this->importJenisEkstrakurikuler($map, $line, $lookups),
                        'sekolah'                      => $this->importSekolah($map, $line, $lookups),
                        'ekstrakurikuler'              => $this->importEkstrakurikuler($map, $line, $lookups),
                        'operators'                    => $this->importOperator($map, $line, $lookups),
                    };

                    if ($result === true) $berhasil++;
                    else $gagal[] = $result;
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                $gagal[] = ['baris' => 'Batch ' . ($batchIdx + 1), 'data' => [], 'alasan' => 'Error batch: ' . $e->getMessage()];
            }
        }

        LogSistem::catat('IMPORT', ucfirst($type),
            "Import {$type}: {$berhasil} berhasil, " . count($gagal) . " gagal (total {$totalRows} baris)");

        return response()->json([
            'total'        => $totalRows,
            'berhasil'     => $berhasil,
            'gagal'        => count($gagal),
            'detail_gagal' => array_slice($gagal, 0, 100),
        ]);
    }

    // ── Per-entity import ──────────────────────────────────

    private function importOrang(array $m, int $line, array $lk): true|array
    {
        $nik  = trim((string)($m['nik'] ?? ''));
        $nama = trim((string)($m['nama'] ?? ''));

        if (!$nama) return ['baris' => $line, 'data' => compact('nik','nama'), 'alasan' => 'Nama wajib diisi'];

        if ($nik && Orang::where('nik', $nik)->exists()) {
            return ['baris' => $line, 'data' => compact('nik','nama'), 'alasan' => 'NIK sudah terdaftar dalam sistem'];
        }

        $gender = strtoupper(trim((string)($m['gender'] ?? '')));

        $domisiliId = $this->lookup($lk['kab_kota'], $m['domisili'] ?? '');
        if (auth()->user()->kab_kota_id && auth()->user()->kab_kota_id != $domisiliId) {
            return ['baris' => $line, 'data' => compact('nik','nama'), 'alasan' => 'Tidak dapat mengimport data untuk Kab/Kota lain'];
        }

        $orang = Orang::create([
            'nik'         => $nik ?: null,
            'nama'        => $nama,
            'tgl_lahir'   => $this->parseDate($m['tgl_lahir'] ?? ''),
            'gender'      => in_array($gender, ['L','P']) ? $gender : null,
            'telp'        => trim((string)($m['telp'] ?? '')) ?: null,
            'alamat'      => trim((string)($m['alamat'] ?? '')) ?: null,
            'domisili_id' => $domisiliId,
            'disabilitas'        => in_array(strtolower(trim((string)($m['disabilitas'] ?? ''))), ['1','ya','true','yes']),
            'jenis_disabilitas'  => in_array(strtolower(trim((string)($m['jenis_disabilitas'] ?? ''))), ['fisik','intelektual','mental','sensorik_netra','sensorik_rungu','ganda']) ? strtolower(trim($m['jenis_disabilitas'])) : null,
            'tinggi'      => is_numeric($m['tinggi'] ?? '') ? (float)$m['tinggi'] : null,
            'berat'       => is_numeric($m['berat'] ?? '') ? (float)$m['berat'] : null,
            'gol_darah'   => in_array(strtoupper(trim((string)($m['gol_darah'] ?? ''))), ['A','B','AB','O']) ? strtoupper(trim($m['gol_darah'])) : null,
        ]);

        // Create orang_status if jenis/peran provided
        $jenisId = $this->lookup($lk['jenis'], $m['jenis'] ?? '');
        $peranId = $this->lookup($lk['peran'], $m['peran'] ?? '');

        if ($jenisId || $peranId) {
            OrangStatus::create([
                'orang_id'      => $orang->id,
                'jenis_id'      => $jenisId,
                'peran_id'      => $peranId,
                'cabor_id'      => $this->lookup($lk['cabor'], $m['cabor'] ?? ''),
                'organisasi_id' => $this->lookup($lk['organisasi'], $m['organisasi'] ?? ''),
                'skala_id'      => $this->lookup($lk['skala'], $m['skala'] ?? ''),
                'is_active'     => true,
            ]);
        }

        return true;
    }

    private function importPrasarana(array $m, int $line, array $lk): true|array
    {
        $nama = trim((string)($m['nama'] ?? ''));
        if (!$nama) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Nama wajib diisi'];

        $lokasiId = $this->lookup($lk['kab_kota'], $m['lokasi'] ?? '');
        if (auth()->user()->kab_kota_id && auth()->user()->kab_kota_id != $lokasiId) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Tidak dapat mengimport data untuk Kab/Kota lain'];
        }

        if (Prasarana::where('nama', $nama)->where('lokasi_id', $lokasiId)->exists()) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Prasarana dengan nama dan lokasi sama sudah ada'];
        }

        Prasarana::create([
            'nama'            => $nama,
            'jenis_id'        => $this->lookup($lk['jenis'], $m['jenis'] ?? ''),
            'lokasi_id'       => $lokasiId,
            'pengelola'       => trim((string)($m['pengelola'] ?? '')) ?: null,
            'narahubung'      => trim((string)($m['narahubung'] ?? '')) ?: null,
            'telp_narahubung' => trim((string)($m['telp_narahubung'] ?? '')) ?: null,
            'alamat'          => trim((string)($m['alamat'] ?? '')) ?: null,
            'kapasitas'       => is_numeric($m['kapasitas'] ?? '') ? (int)$m['kapasitas'] : null,
            'keterangan'      => trim((string)($m['keterangan'] ?? '')) ?: null,
        ]);

        return true;
    }

    private function importEvent(array $m, int $line, array $lk): true|array
    {
        $nama = trim((string)($m['nama'] ?? ''));
        if (!$nama) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Nama event wajib diisi'];

        $penyelenggara = trim((string)($m['penyelenggara'] ?? ''));
        if (!$penyelenggara) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Penyelenggara wajib diisi'];

        $jenisEvent = strtolower(trim((string)($m['jenis_event'] ?? '')));
        $valid = ['single event','multi event','pelatihan','perlombaan'];
        if (!in_array($jenisEvent, $valid)) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Jenis event tidak valid. Pilih: ' . implode(', ', $valid)];
        }

        $tglMulai = $this->parseDate($m['tanggal_mulai'] ?? '');
        if ($tglMulai && Event::where('nama', $nama)->whereDate('tanggal_mulai', $tglMulai)->exists()) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Event dengan nama dan tanggal mulai sama sudah ada'];
        }

        $status = strtolower(trim((string)($m['status'] ?? 'aktif')));
        if (!in_array($status, ['aktif','selesai','dibatalkan'])) $status = 'aktif';

        Event::create([
            'nama'             => $nama,
            'jenis_id'         => $this->lookup($lk['jenis'], $m['jenis'] ?? ''),
            'skala_id'         => $this->lookup($lk['skala'], $m['skala'] ?? ''),
            'jenis_event'      => $jenisEvent,
            'penyelenggara'    => $penyelenggara,
            'lokasi_kegiatan'  => trim((string)($m['lokasi_kegiatan'] ?? '')) ?: null,
            'tanggal_mulai'    => $tglMulai,
            'tanggal_selesai'  => $this->parseDate($m['tanggal_selesai'] ?? ''),
            'status'           => $status,
        ]);

        return true;
    }

    private function importOrganisasi(array $m, int $line, array $lk): true|array
    {
        $nama = trim((string)($m['nama'] ?? ''));
        if (!$nama) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Nama organisasi wajib diisi'];

        $jenisId = $this->lookup($lk['jenis'], $m['jenis'] ?? '');

        if (Organisasi::where('nama', $nama)->where('jenis_id', $jenisId)->exists()) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Organisasi dengan nama dan jenis sama sudah ada'];
        }

        $status = ucfirst(strtolower(trim((string)($m['status'] ?? 'Aktif'))));
        if (!in_array($status, ['Aktif','Non-aktif'])) $status = 'Aktif';

        $kabKotaId = $this->lookup($lk['kab_kota'], $m['kab_kota'] ?? '');
        if (auth()->user()->kab_kota_id && auth()->user()->kab_kota_id != $kabKotaId) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Tidak dapat mengimport data untuk Kab/Kota lain'];
        }

        Organisasi::create([
            'nama'             => $nama,
            'jenis_id'         => $jenisId,
            'alamat'           => trim((string)($m['alamat'] ?? '')) ?: null,
            'telp'             => trim((string)($m['telp'] ?? '')) ?: null,
            'email'            => trim((string)($m['email'] ?? '')) ?: null,
            'narahubung'       => trim((string)($m['narahubung'] ?? '')) ?: null,
            'sk_pendirian'     => trim((string)($m['sk_pendirian'] ?? '')) ?: null,
            'tgl_sk_pendirian' => $this->parseDate($m['tgl_sk_pendirian'] ?? ''),
            'kab_kota_id'      => $kabKotaId,
            'status'           => $status,
            'skala_id'         => $this->lookup($lk['skala'], $m['skala'] ?? ''),
        ]);

        return true;
    }

    private function importSarana(array $m, int $line, array $lk): true|array
    {
        $nama = trim((string)($m['nama_barang'] ?? ''));
        if (!$nama) return ['baris' => $line, 'data' => ['nama_barang' => $nama], 'alasan' => 'Nama barang wajib diisi'];

        $kode = trim((string)($m['kode_inventaris'] ?? ''));
        if ($kode && Sarana::where('kode_inventaris', $kode)->exists()) {
            return ['baris' => $line, 'data' => ['kode_inventaris' => $kode], 'alasan' => 'Kode inventaris sudah terdaftar'];
        }

        $kondisi = strtolower(trim((string)($m['kondisi'] ?? 'baik')));
        if (!in_array($kondisi, ['baik','rusak_ringan','rusak_berat','butuh_perbaikan','dalam_perbaikan','tidak_layak'])) $kondisi = 'baik';

        $status = strtolower(trim((string)($m['status'] ?? 'tersedia')));
        if (!in_array($status, ['tersedia','dipakai','dipinjam','dipelihara','hilang','rusak_total','dijual','dimusnahkan'])) $status = 'tersedia';

        $posisi = strtolower(trim((string)($m['posisi_aset'] ?? 'internal_dinas')));
        if (!in_array($posisi, ['prasarana','internal_dinas'])) $posisi = 'internal_dinas';

        $kabKotaId = $this->lookup($lk['kab_kota'], $m['kab_kota'] ?? '');
        if (auth()->user()->kab_kota_id && auth()->user()->kab_kota_id != $kabKotaId) {
            return ['baris' => $line, 'data' => ['kode_inventaris' => $kode], 'alasan' => 'Tidak dapat mengimport data untuk Kab/Kota lain'];
        }

        Sarana::create([
            'nama_barang'       => $nama,
            'kode_inventaris'   => $kode ?: null,
            'jenis_id'          => $this->lookup($lk['jenis'], $m['jenis'] ?? ''),
            'cabor_id'          => $this->lookup($lk['cabor'], $m['cabor'] ?? ''),
            'kondisi'           => $kondisi,
            'status'            => $status,
            'posisi_aset'       => $posisi,
            'lokasi_barang'     => $this->lookup($lk['prasarana'], $m['lokasi_barang'] ?? ''),
            'keterangan_lokasi' => trim((string)($m['keterangan_lokasi'] ?? '')) ?: null,
            'jumlah'            => is_numeric($m['jumlah'] ?? '') ? (int)$m['jumlah'] : 1,
            'satuan'            => trim((string)($m['satuan'] ?? '')) ?: 'buah',
            'tahun_pengadaan'   => is_numeric($m['tahun_pengadaan'] ?? '') ? (int)$m['tahun_pengadaan'] : null,
            'sumber_dana'       => trim((string)($m['sumber_dana'] ?? '')) ?: null,
            'kab_kota_id'       => $kabKotaId,
        ]);

        return true;
    }

    // ── Helpers ────────────────────────────────────────────

    private function buildLookups(): array
    {
        return \Illuminate\Support\Facades\Cache::remember('import_lookups', 60, function() {
            $norm = fn($col) => $col->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])->toArray();
            return [
                'kab_kota'    => $norm(KabKota::pluck('id', 'name')),
                'jenis'       => $norm(Jenis::pluck('id', 'nama')),
                'peran'       => $norm(Peran::pluck('id', 'nama')),
                'cabor'       => $norm(Cabor::pluck('id', 'nama')),
                'skala'       => $norm(Skala::pluck('id', 'nama')),
                'organisasi'  => $norm(Organisasi::pluck('id', 'nama')),
                'prasarana'   => $norm(Prasarana::pluck('id', 'nama')),
                'sekolah'       => $norm(Sekolah::pluck('id', 'nama_sekolah')),
                'jenis_ekskul'  => $norm(JenisEkstrakurikuler::pluck('id', 'nama')),
                'role'          => $norm(\Spatie\Permission\Models\Role::pluck('id', 'name')),
            ];
        });
    }

    private function importJenisEkstrakurikuler(array $m, int $line, array $lk): true|array
    {
        $nama = trim((string)($m['nama'] ?? ''));
        if (!$nama) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Nama wajib diisi'];

        $kategori = strtolower(str_replace(' ', '_', trim((string)($m['kategori'] ?? 'olahraga'))));
        $validKategori = ['olahraga', 'kepemimpinan', 'seni_budaya', 'akademik_sains', 'keagamaan'];
        if (!in_array($kategori, $validKategori)) $kategori = 'olahraga';

        JenisEkstrakurikuler::updateOrCreate(
            ['nama' => $nama],
            [
                'kategori'   => $kategori,
                'cabor_id'   => $this->lookup($lk['cabor'], $m['cabor_terkait'] ?? ''),
                'keterangan' => trim((string)($m['keterangan'] ?? '')) ?: null,
                'is_active'  => in_array(strtolower(trim((string)($m['status_aktif'] ?? '1'))), ['1', 'aktif', 'ya', 'yes', 'true']),
            ]
        );

        return true;
    }

    private function importSekolah(array $m, int $line, array $lk): true|array
    {
        $nama = trim((string)($m['nama_sekolah'] ?? ''));
        if (!$nama) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Nama sekolah wajib diisi'];

        $kabKotaId = $this->lookup($lk['kab_kota'], $m['kab_kota'] ?? '');
        if (!$kabKotaId) return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Kab/Kota tidak valid atau kosong'];

        // Enforce tenancy jika bukan superadmin
        if (auth()->user()->kab_kota_id && auth()->user()->kab_kota_id != $kabKotaId) {
            return ['baris' => $line, 'data' => ['nama' => $nama], 'alasan' => 'Tidak dapat mengimport data untuk Kab/Kota lain'];
        }

        $jenis = strtoupper(trim((string)($m['jenis_sekolah'] ?? 'SMA')));
        if (!in_array($jenis, ['SMA', 'SMK', 'MA', 'SLB'])) $jenis = 'SMA';

        $status = ucfirst(strtolower(trim((string)($m['status_sekolah'] ?? 'Negeri'))));
        if (!in_array($status, ['Negeri', 'Swasta'])) $status = 'Negeri';

        Sekolah::updateOrCreate(
            ['nama_sekolah' => $nama, 'kab_kota_id' => $kabKotaId],
            [
                'jenis_sekolah'  => $jenis,
                'status_sekolah' => $status,
                'narahubung'     => trim((string)($m['narahubung'] ?? '')) ?: null,
                'telepon'        => trim((string)($m['telepon'] ?? '')) ?: null,
                'created_by'     => auth()->id(),
            ]
        );

        return true;
    }

    private function importEkstrakurikuler(array $m, int $line, array $lk): true|array
    {
        $sekolahId = $this->lookup($lk['sekolah'], $m['nama_sekolah'] ?? '');
        if (!$sekolahId) return ['baris' => $line, 'data' => ['sekolah' => $m['nama_sekolah'] ?? ''], 'alasan' => 'Nama sekolah tidak ditemukan di sistem'];

        $jenisId = $this->lookup($lk['jenis_ekskul'], $m['jenis_ekskul'] ?? '');
        if (!$jenisId) return ['baris' => $line, 'data' => ['jenis_ekskul' => $m['jenis_ekskul'] ?? ''], 'alasan' => 'Jenis ekskul tidak valid'];

        $pembina = trim((string)($m['nama_pembina'] ?? ''));
        if (!$pembina) return ['baris' => $line, 'data' => [], 'alasan' => 'Nama pembina wajib diisi'];

        $status = ucfirst(strtolower(trim((string)($m['status'] ?? 'Aktif'))));
        if (!in_array($status, ['Aktif', 'Non-aktif'])) $status = 'Aktif';

        EkstrakurikulerSekolah::updateOrCreate(
            ['sekolah_id' => $sekolahId, 'jenis_ekskul_id' => $jenisId],
            [
                'nama_pembina'           => $pembina,
                'jumlah_anggota_putra'   => is_numeric($m['jumlah_anggota_putra'] ?? '') ? (int)$m['jumlah_anggota_putra'] : 0,
                'jumlah_anggota_putri'   => is_numeric($m['jumlah_anggota_putri'] ?? '') ? (int)$m['jumlah_anggota_putri'] : 0,
                'jadwal_pertemuan'       => trim((string)($m['jadwal_pertemuan'] ?? '')) ?: null,
                'status_ekstrakurikuler' => $status,
                'narahubung'             => trim((string)($m['narahubung'] ?? '')) ?: null,
                'telepon'                => trim((string)($m['telepon'] ?? '')) ?: null,
                'created_by'             => auth()->id(),
            ]
        );

        return true;
    }

    private function importOperator(array $m, int $line, array $lk): true|array
    {
        $nik = trim((string)($m['nik'] ?? ''));
        $nama = trim((string)($m['nama'] ?? ''));

        if (!$nik || strlen($nik) !== 16 || !ctype_digit($nik)) {
            return ['baris' => $line, 'data' => ['nik' => $nik], 'alasan' => 'NIK wajib diisi dengan 16 digit angka'];
        }

        if (!$nama) return ['baris' => $line, 'data' => ['nik' => $nik], 'alasan' => 'Nama wajib diisi'];

        if (\App\Models\Operator::where('nik', $nik)->exists()) {
            return ['baris' => $line, 'data' => ['nik' => $nik], 'alasan' => 'NIK sudah terdaftar sebagai operator'];
        }

        $roleId = $this->lookup($lk['role'], $m['role'] ?? '');
        if (!$roleId) return ['baris' => $line, 'data' => ['role' => $m['role'] ?? ''], 'alasan' => 'Role tidak ditemukan'];

        $skalaId = $this->lookup($lk['skala'], $m['skala'] ?? '');
        if (!$skalaId) return ['baris' => $line, 'data' => ['skala' => $m['skala'] ?? ''], 'alasan' => 'Skala tidak valid'];

        // Cek skala daerah wajib kab/kota
        $skalaObj = Skala::find($skalaId);
        $kabkotaId = $this->lookup($lk['kab_kota'], $m['kab_kota'] ?? '');
        
        if ($skalaObj && strtolower($skalaObj->nama) === 'daerah' && !$kabkotaId) {
            return ['baris' => $line, 'data' => [], 'alasan' => 'Skala Daerah mewajibkan pengisian Kab/Kota yang valid'];
        }

        \App\Models\Operator::create([
            'nik'        => $nik,
            'nama'       => $nama,
            'nip'        => trim((string)($m['nip'] ?? '')) ?: null,
            'jabatan'    => trim((string)($m['jabatan'] ?? '')) ?: '-',
            'role_id'    => $roleId,
            'skala_id'   => $skalaId,
            'kabkota_id' => strtolower($skalaObj?->nama ?? '') === 'daerah' ? $kabkotaId : null,
            'cabor_id'   => $this->lookup($lk['cabor'], $m['cabor'] ?? ''),
            'email'      => trim((string)($m['email'] ?? '')) ?: null,
            'no_telp'    => trim((string)($m['no_telp'] ?? '')) ?: null,
        ]);

        return true;
    }

    private function lookup(array $table, ?string $value): ?int
    {
        if (!$value || !trim((string)$value)) return null;
        return $table[strtolower(trim((string)$value))] ?? null;
    }

    private function parseDate(?string $v): ?string
    {
        if (!$v || !trim((string)$v)) return null;
        $v = trim((string)$v);
        if (is_numeric($v)) {
            try { return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int)$v)->format('Y-m-d'); }
            catch (\Throwable) { return null; }
        }
        try { return date('Y-m-d', strtotime($v)); }
        catch (\Throwable) { return null; }
    }
}
