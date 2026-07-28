<?php

namespace Database\Seeders;

use App\Models\Cabor;
use App\Models\Jenis;
use App\Models\KabKota;
use App\Models\Orang;
use App\Models\OrangStatus;
use App\Models\Peran;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ImportOrangSeeder
 *
 * Mengimport data orang dari backup database lama (sportif_atlets, sportif_pelatihs,
 * sportif_instrukturs, sportif_wasit_juris) ke tabel terpadu orang + orang_status.
 *
 * Arsitektur baru:
 *   1 orang => banyak orang_status (multi-peran per orang)
 *   NIK digunakan sebagai kunci deduplikasi
 */
class ImportOrangSeeder extends Seeder
{
    // Path ke file SQL backup
    private string $sqlFile = 'F:\\SPORTIF\\sportif-v2-master\\data sportif.sql';

    // Cache lookup agar tidak query berulang
    private array $kabKotaMap    = []; // old_id => new_id
    private array $caborMap      = []; // old_id => new_id
    private array $peranCache    = []; // "jenis_id:nama" => peran_id
    private ?int  $jenisPrestasiId = null;
    private ?int  $peranAtletId    = null;
    private ?int  $peranPelatihId  = null;
    private ?int  $peranInstrukturId = null;
    private ?int  $peranWasitId    = null;
    private array $orangNikMap   = []; // nik => orang_id (untuk deduplikasi)

    public function run(): void
    {
        if (!file_exists($this->sqlFile)) {
            $this->command->warn('File backup SQL tidak ditemukan: ' . $this->sqlFile);
            $this->command->warn('Lewati import data orang dari backup.');
            return;
        }

        $this->command->info('Memuat peta referensi...');
        $this->buildMaps();

        $sql = file_get_contents($this->sqlFile);

        $this->command->info('Mengimport atlet dari backup...');
        $this->importAtlets($sql);

        $this->command->info('Mengimport pelatih dari backup...');
        $this->importPelatihs($sql);

        $this->command->info('Mengimport instruktur dari backup...');
        $this->importInstrukturs($sql);

        $this->command->info('Mengimport wasit/juri dari backup...');
        $this->importWasitJuris($sql);

        $this->command->info('Import selesai. Total orang: ' . Orang::count());
    }

    /**
     * Bangun peta ID kab_kota dan cabor dari backup ke ID baru.
     * Kab/kota dicocokkan berdasarkan code, cabor berdasarkan nama.
     */
    private function buildMaps(): void
    {
        $jenisOlpres = Jenis::where('nama', 'Olahraga Prestasi')->first();
        $this->jenisPrestasiId = $jenisOlpres->id;

        $this->peranAtletId      = Peran::where('jenis_id', $this->jenisPrestasiId)->where('nama', 'Atlet')->value('id');
        $this->peranPelatihId    = Peran::where('jenis_id', $this->jenisPrestasiId)->where('nama', 'Pelatih')->value('id');
        $this->peranInstrukturId = Peran::where('jenis_id', $this->jenisPrestasiId)->where('nama', 'Instruktur')->value('id');
        $this->peranWasitId      = Peran::where('jenis_id', $this->jenisPrestasiId)->where('nama', 'Wasit/Juri')->value('id');

        // Peta kab_kota: posisi di backup berurut 1-38 persis sama dengan seeder baru
        // Karena urutan insert sama, ID baru == ID lama
        $this->kabKotaMap = KabKota::pluck('id', 'id')->toArray();

        // Peta cabor berdasarkan nama (lowercase strip)
        $allCabor = Cabor::all();
        foreach ($allCabor as $c) {
            // Key: normalized nama
            $key = $this->normalizeName($c->nama);
            $this->caborMap[$key] = $c->id;
        }
    }

    /**
     * Petakan ID cabor lama (posisi) ke ID baru menggunakan nama.
     * Urutan insert backup sama dengan seeder, sehingga old_id sama.
     */
    private function mapCaborId(int $oldId): ?int
    {
        // ID cabor backup = 1..73, ID baru juga dimulai dari 1 dengan urutan sama
        $cabor = Cabor::find($oldId);
        return $cabor?->id;
    }

    /**
     * Petakan ID kab_kota lama ke baru.
     */
    private function mapKabKotaId(int $oldId): ?int
    {
        return KabKota::find($oldId)?->id;
    }

    /**
     * Cari atau buat record orang berdasarkan NIK (deduplikasi).
     * Return orang_id.
     */
    private function upsertOrang(array $data): int
    {
        $nik = trim($data['nik'] ?? '');

        // Jika NIK valid (16 digit numerik), gunakan sebagai kunci deduplikasi
        if ($nik && strlen(preg_replace('/\D/', '', $nik)) === 16) {
            if (isset($this->orangNikMap[$nik])) {
                return $this->orangNikMap[$nik];
            }
            $existing = Orang::where('nik', $nik)->first();
            if ($existing) {
                $this->orangNikMap[$nik] = $existing->id;
                return $existing->id;
            }
        }

        // Sanitasi: konversi string kosong ke null untuk field nullable/ENUM
        $golDarah = $data['gol_darah'] ?? null;
        if (!in_array($golDarah, ['A', 'B', 'AB', 'O'], true)) {
            $golDarah = null;
        }

        $gender = $data['gender'] ?? null;
        if (!in_array($gender, ['L', 'P'], true)) {
            $gender = null;
        }

        // Buat baru
        $orang = Orang::create([
            'nik'         => $nik ?: null,
            'nama'        => $data['nama'],
            'tgl_lahir'   => !empty($data['tgl_lahir']) ? $data['tgl_lahir'] : null,
            'gender'      => $gender,
            'foto'        => !empty($data['foto']) ? $data['foto'] : null,
            'alamat'      => !empty($data['alamat']) ? $data['alamat'] : null,
            'telp'        => !empty($data['telp']) ? $data['telp'] : null,
            'tinggi'      => !empty($data['tinggi']) ? $data['tinggi'] : null,
            'berat'       => !empty($data['berat']) ? $data['berat'] : null,
            'gol_darah'   => $golDarah,
            'domisili_id' => $data['domisili_id'] ?? null,
            'difabel'     => $data['difabel'] ?? false,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        if ($nik && strlen(preg_replace('/\D/', '', $nik)) === 16) {
            $this->orangNikMap[$nik] = $orang->id;
        }

        return $orang->id;
    }

    /**
     * Tambahkan status orang (hindari duplikasi jenis+peran+cabor per orang).
     */
    private function addStatus(int $orangId, int $jenisId, int $peranId, ?int $caborId, ?string $idSitenor = null): void
    {
        $exists = OrangStatus::where([
            'orang_id' => $orangId,
            'jenis_id' => $jenisId,
            'peran_id' => $peranId,
            'cabor_id' => $caborId,
        ])->exists();

        if (!$exists) {
            OrangStatus::create([
                'orang_id'   => $orangId,
                'jenis_id'   => $jenisId,
                'peran_id'   => $peranId,
                'cabor_id'   => $caborId,
                'id_sitenor' => $idSitenor,
                'is_active'  => true,
            ]);
        }
    }

    /**
     * Import dari tabel sportif_atlets + sportif_atlet_details.
     */
    private function importAtlets(string $sql): void
    {
        // Ekstrak data atlet dengan regex
        preg_match_all(
            '/\((\d+),\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*\'([^\']*)\',\s*(NULL|\'[^\']*\')\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*\'([^\']*)\'/m',
            $this->extractInsert($sql, 'sportif_atlets'),
            $matches,
            PREG_SET_ORDER
        );

        // Parse atlet_details untuk alamat dan fisik
        $details = $this->parseAtletDetails($sql);

        $count = 0;
        foreach ($matches as $m) {
            [$full, $id, $nik, $nama, $tglLahir, $gender, $fotoRaw, $kabKotaOldId, $caborOldId, $status] = $m;
            $foto = ($fotoRaw !== 'NULL') ? trim($fotoRaw, "'") : null;
            $detail = $details[$id] ?? [];

            $domisiliId = $this->mapKabKotaId((int) $kabKotaOldId);
            $caborId    = $this->mapCaborId((int) $caborOldId);

            $orangId = $this->upsertOrang([
                'nik'         => $nik,
                'nama'        => $nama,
                'tgl_lahir'   => $tglLahir,
                'gender'      => $gender,
                'foto'        => $foto,
                'alamat'      => $detail['address'] ?? null,
                'telp'        => $detail['phone'] ?? null,
                'tinggi'      => $detail['height'] ?? null,
                'berat'       => $detail['weight'] ?? null,
                'gol_darah'   => $detail['blood_type'] ?? null,
                'domisili_id' => $domisiliId,
                'is_active'   => ($status === 'active'),
            ]);

            $this->addStatus($orangId, $this->jenisPrestasiId, $this->peranAtletId, $caborId);
            $count++;
        }
        $this->command->info("  -> {$count} atlet diimport.");
    }

    /**
     * Parse tabel sportif_atlet_details untuk data fisik/alamat.
     */
    private function parseAtletDetails(string $sql): array
    {
        $insertBlock = $this->extractInsert($sql, 'sportif_atlet_details');
        if (!$insertBlock) return [];

        preg_match_all(
            '/\((\d+),\s*(\d+),\s*(NULL|[\d.]+),\s*(NULL|[\d.]+),\s*(NULL|\'[^\']*\'),\s*(NULL|\'(?:[^\']|\'\')*\')/m',
            $insertBlock,
            $m,
            PREG_SET_ORDER
        );

        $result = [];
        foreach ($m as $row) {
            $atletId    = (int) $row[2];
            $height     = $row[3] !== 'NULL' ? (float) $row[3] : null;
            $weight     = $row[4] !== 'NULL' ? (float) $row[4] : null;
            $bloodType  = $row[5] !== 'NULL' ? trim($row[5], "'") : null;
            $address    = $row[6] !== 'NULL' ? trim($row[6], "'") : null;
            $result[$atletId] = [
                'height'     => $height,
                'weight'     => $weight,
                'blood_type' => $bloodType,
                'address'    => $address,
            ];
        }
        return $result;
    }

    /**
     * Import dari tabel sportif_pelatihs.
     */
    private function importPelatihs(string $sql): void
    {
        $insertBlock = $this->extractInsert($sql, 'sportif_pelatihs');
        if (!$insertBlock) { $this->command->warn('  -> Tabel sportif_pelatihs tidak ditemukan.'); return; }

        // Kolom: id, nik, nama, jenis_kelamin, cabor_id, kab_kota_id, kontak, event, id_sitenor, photo, status
        preg_match_all(
            "/\((\d+),\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*(NULL|'[^']*')\s*,\s*(NULL|'[^']*')\s*,\s*(NULL|'[^']*')\s*,\s*(NULL|'[^']*')\s*,\s*'([^']*)'/m",
            $insertBlock,
            $matches,
            PREG_SET_ORDER
        );

        $count = 0;
        foreach ($matches as $m) {
            [, $id, $nik, $nama, $gender, $caborOldId, $kabKotaOldId, $kontak, , $idSitenor, $photo, $status] = $m;
            $kontak     = $this->unwrapNullable($kontak);
            $idSitenor  = $this->unwrapNullable($idSitenor);
            $photo      = $this->unwrapNullable($photo);
            $domisiliId = $this->mapKabKotaId((int) $kabKotaOldId);
            $caborId    = $this->mapCaborId((int) $caborOldId);

            $orangId = $this->upsertOrang([
                'nik'         => $nik,
                'nama'        => $nama,
                'gender'      => $gender,
                'telp'        => $kontak,
                'foto'        => $photo,
                'domisili_id' => $domisiliId,
                'is_active'   => ($status === 'active'),
            ]);

            $this->addStatus($orangId, $this->jenisPrestasiId, $this->peranPelatihId, $caborId, $idSitenor);
            $count++;
        }
        $this->command->info("  -> {$count} pelatih diimport.");
    }

    /**
     * Import dari tabel sportif_instrukturs.
     */
    private function importInstrukturs(string $sql): void
    {
        $insertBlock = $this->extractInsert($sql, 'sportif_instrukturs');
        if (!$insertBlock) { $this->command->warn('  -> Tabel sportif_instrukturs tidak ditemukan.'); return; }

        // Kolom: id, nik, nama, tanggal_lahir, jenis_kelamin, domisili(kab_kota_id), alamat, cabor_id, foto_path, status
        preg_match_all(
            "/\((\d+),\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*(\d+)\s*,\s*'([^']*)'\s*,\s*(\d+)\s*,\s*(NULL|'[^']*')\s*,\s*'([^']*)'/m",
            $insertBlock,
            $matches,
            PREG_SET_ORDER
        );

        $count = 0;
        foreach ($matches as $m) {
            [, $id, $nik, $nama, $tglLahir, $gender, $kabKotaOldId, $alamat, $caborOldId, $photo, $status] = $m;
            $photo      = $this->unwrapNullable($photo);
            $domisiliId = $this->mapKabKotaId((int) $kabKotaOldId);
            $caborId    = $this->mapCaborId((int) $caborOldId);

            $orangId = $this->upsertOrang([
                'nik'         => $nik,
                'nama'        => $nama,
                'tgl_lahir'   => $tglLahir,
                'gender'      => $gender,
                'alamat'      => $alamat,
                'foto'        => $photo,
                'domisili_id' => $domisiliId,
                'is_active'   => ($status === 'active'),
            ]);

            $this->addStatus($orangId, $this->jenisPrestasiId, $this->peranInstrukturId, $caborId);
            $count++;
        }
        $this->command->info("  -> {$count} instruktur diimport.");
    }

    /**
     * Import dari tabel sportif_wasit_juris.
     * Kolom: id, nik, no_wasit_juri, nama, tanggal_lahir, jenis_kelamin, domisili, alamat, cabor_id, foto_path, status
     */
    private function importWasitJuris(string $sql): void
    {
        $insertBlock = $this->extractInsert($sql, 'sportif_wasit_juris');
        if (!$insertBlock) { $this->command->warn('  -> Tabel sportif_wasit_juris tidak ditemukan.'); return; }

        // id, nik, no_wasit_juri, nama, tanggal_lahir, jenis_kelamin, domisili(kab_kota_id), alamat, cabor_id, foto_path, status
        preg_match_all(
            "/\((\d+),\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*(\d+)\s*,\s*'([^']*)'\s*,\s*(\d+)/m",
            $insertBlock,
            $matches,
            PREG_SET_ORDER
        );

        $count = 0;
        foreach ($matches as $m) {
            [, $id, $nik, $noWasit, $nama, $tglLahir, $gender, $kabKotaOldId, $alamat, $caborOldId] = $m;
            $domisiliId = $this->mapKabKotaId((int) $kabKotaOldId);
            $caborId    = $this->mapCaborId((int) $caborOldId);

            $orangId = $this->upsertOrang([
                'nik'         => $nik,
                'nama'        => $nama,
                'tgl_lahir'   => $tglLahir,
                'gender'      => $gender,
                'alamat'      => $alamat,
                'domisili_id' => $domisiliId,
            ]);

            // no_wasit_juri disimpan sebagai id_sitenor (nomor lisensi/sertifikat)
            $this->addStatus($orangId, $this->jenisPrestasiId, $this->peranWasitId, $caborId, $noWasit);
            $count++;
        }
        $this->command->info("  -> {$count} wasit/juri diimport.");
    }

    /**
     * Ekstrak blok INSERT VALUES dari SQL dump untuk tabel tertentu.
     */
    private function extractInsert(string $sql, string $tableName): ?string
    {
        $pattern = '/INSERT INTO `' . preg_quote($tableName, '/') . '`\s+\([^)]+\)\s+VALUES\s*([\s\S]+?);(?=\s*(?:--|CREATE|ALTER|DROP|\/\*|\z))/';
        if (preg_match($pattern, $sql, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * Bersihkan nilai nullable dari SQL dump (NULL atau 'value').
     */
    private function unwrapNullable(string $val): ?string
    {
        if ($val === 'NULL') return null;
        return trim($val, "'");
    }

    private function normalizeName(string $name): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/i', '', $name));
    }
}
