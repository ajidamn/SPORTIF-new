<?php

namespace Database\Seeders;

use App\Models\FasilitasPrasarana;
use App\Models\FotoPrasarana;
use App\Models\KabKota;
use App\Models\Prasarana;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportPrasaranaSeeder extends Seeder
{
    private string $sqlFile;

    // old kota_id (dari backup) → kab_kota.id baru
    // Di backup: venue.kota = index 1-38 berurutan sesuai urutan INSERT kab_kota lama
    // Kita rebuild map berdasarkan nama kota yang ada
    private array $kotaMap = [];   // old_kota_id → new kab_kota.id
    private array $caborMap = [];  // old_cabor_id → new cabors.id (old_id == new_id)
    private array $venueMap = [];  // old_venue_id → new prasarana.id

    public function run(): void
    {
        $this->sqlFile = base_path('data sportif.sql');
        if (!file_exists($this->sqlFile)) {
            $this->command->error('File SQL tidak ditemukan');
            return;
        }

        $this->command->info('Membangun peta kota...');
        $this->buildKotaMap();

        $this->command->info('Membangun peta cabor...');
        $this->buildCaborMap();

        $this->command->info('Mengimport venues → prasarana...');
        $this->importVenues();

        $this->command->info('Mengimport venue_cabor → cabor_prasarana...');
        $this->importVenueCabor();

        $this->command->info('Memecah kolom fasilitas → fasilitas_prasarana...');
        $this->importFasilitas();

        $this->command->info('Mengimport venue_fotos → foto_prasarana...');
        $this->importFotos();
    }

    // ── Kota Map ──────────────────────────────────────────────────────────────
    // backup: kota = angka 1-38+ urutan dari INSERT sportif_kab_kotas
    // Kita ambil urutan INSERT kab_kotas dari backup lalu map ke id baru berdasarkan nama

    private function buildKotaMap(): void
    {
        $sql = file_get_contents($this->sqlFile);

        // Ekstrak INSERT sportif_kab_kotas
        if (!preg_match('/INSERT INTO `sportif_kab_kotas`[^;]+;/s', $sql, $block)) {
            $this->command->warn('sportif_kab_kotas tidak ditemukan — menggunakan urutan id langsung');
            // Fallback: asumsikan id sama
            KabKota::all()->each(fn($k) => $this->kotaMap[$k->id] = $k->id);
            return;
        }

        // Ekstrak (id, nama) dari setiap baris
        preg_match_all('/\((\d+),\s*\'([^\']+)\'/m', $block[0], $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            [, $oldId, $nama] = $m;
            // Cari di database baru berdasarkan nama (partial match)
            $kk = KabKota::where('name', 'like', '%' . trim($nama) . '%')->first()
               ?? KabKota::where('name', trim($nama))->first();
            if ($kk) {
                $this->kotaMap[(int)$oldId] = $kk->id;
            }
        }

        // Fallback: untuk id yang tidak terpetakan, coba by id langsung
        $allKk = KabKota::pluck('id', 'id')->toArray();
        for ($i = 1; $i <= 40; $i++) {
            if (!isset($this->kotaMap[$i]) && isset($allKk[$i])) {
                $this->kotaMap[$i] = $i;
            }
        }

        $this->command->info('  -> ' . count($this->kotaMap) . ' kota terpetakan');
    }

    // ── Cabor Map ─────────────────────────────────────────────────────────────

    private function buildCaborMap(): void
    {
        // Cabor old_id == new id (sama persis karena seeder sama)
        \App\Models\Cabor::all()->each(fn($c) => $this->caborMap[$c->id] = $c->id);
        $this->command->info('  -> ' . count($this->caborMap) . ' cabor terpetakan');
    }

    // ── Import Venues → Prasarana ─────────────────────────────────────────────

    private function importVenues(): void
    {
        $sql = file_get_contents($this->sqlFile);

        if (!preg_match('/INSERT INTO `sportif_venues`[^;]+;/s', $sql, $block)) {
            $this->command->warn('Tabel sportif_venues tidak ditemukan');
            return;
        }

        // (id, nama, alamat, kota, latitude, longitude, kapasitas, fasilitas, kontak_pengelola, foto, keterangan, ...)
        preg_match_all(
            "/\((\d+),\s*'([^']*)',\s*'([^']*)',\s*(\d+),\s*([^,]+),\s*([^,]+),\s*(\d+),\s*(NULL|'[^']*'),\s*(NULL|'[^']*'),\s*(NULL|'[^']*'),\s*(NULL|'[^']*'),\s*'([^']+)',\s*'([^']+)',\s*(NULL|'[^']+')\)/m",
            $block[0],
            $matches,
            PREG_SET_ORDER
        );

        $count   = 0;
        $skipped = 0;

        foreach ($matches as $m) {
            [, $oldId, $nama, $alamat, $kotaId, $lat, $lng, $kapasitas,
             $fasilitas, $kontak, $foto, $keterangan, $createdAt, $updatedAt, $deletedAt] = $m;

            // Skip yang sudah soft-deleted di backup
            if ($deletedAt !== 'NULL') { $skipped++; continue; }

            // Skip duplikat berdasarkan nama
            if (Prasarana::where('nama', $nama)->exists()) {
                $this->venueMap[(int)$oldId] = Prasarana::where('nama', $nama)->value('id');
                $skipped++;
                continue;
            }

            $lokasiId = $this->kotaMap[(int)$kotaId] ?? null;

            $latVal  = ($lat  !== 'NULL' && $lat  !== '0.00000000') ? (float)$lat  : null;
            $lngVal  = ($lng  !== 'NULL' && $lng  !== '0.00000000') ? (float)$lng  : null;
            $kontak  = trim($kontak, "'");
            $ket     = $keterangan === 'NULL' ? null : trim($keterangan, "'");
            $fotoVal = $foto === 'NULL' ? null : trim($foto, "'");

            $prasarana = Prasarana::create([
                'nama'            => $nama,
                'alamat'          => $alamat,
                'lokasi_id'       => $lokasiId,
                'latitude'        => $latVal,
                'longitude'       => $lngVal,
                'kapasitas'       => (int)$kapasitas ?: null,
                'narahubung'      => $kontak ?: null,
                'telp_narahubung' => $kontak ?: null,
                'keterangan'      => $ket,
                'pengelola'       => 'Pemerintah', // default
            ]);

            $this->venueMap[(int)$oldId] = $prasarana->id;
            $count++;
        }

        $this->command->info("  -> {$count} prasarana diimport, {$skipped} dilewati (deleted/duplikat)");
    }

    // ── Import Venue-Cabor → cabor_prasarana ─────────────────────────────────

    private function importVenueCabor(): void
    {
        $sql = file_get_contents($this->sqlFile);

        if (!preg_match('/INSERT INTO `sportif_venue_cabor`[^;]+;/s', $sql, $block)) {
            $this->command->warn('sportif_venue_cabor tidak ditemukan');
            return;
        }

        preg_match_all('/\(\d+,\s*(\d+),\s*(\d+)\)/m', $block[0], $matches, PREG_SET_ORDER);

        // Kumpulkan dulu per prasarana agar bisa sync sekaligus
        $map = [];
        foreach ($matches as $m) {
            [, $oldVenueId, $oldCaborId] = $m;
            $prasaranaId = $this->venueMap[(int)$oldVenueId] ?? null;
            $newCaborId  = $this->caborMap[(int)$oldCaborId] ?? null;
            if ($prasaranaId && $newCaborId) {
                $map[$prasaranaId][] = $newCaborId;
            }
        }

        $count = 0;
        foreach ($map as $prasaranaId => $caborIds) {
            $prasarana = Prasarana::find($prasaranaId);
            if ($prasarana) {
                $prasarana->cabors()->syncWithoutDetaching(array_unique($caborIds));
                $count += count($caborIds);
            }
        }

        $this->command->info("  -> {$count} relasi cabor_prasarana dibuat");
    }

    // ── Pecah Kolom fasilitas → fasilitas_prasarana ───────────────────────────

    private function importFasilitas(): void
    {
        $sql = file_get_contents($this->sqlFile);

        if (!preg_match('/INSERT INTO `sportif_venues`[^;]+;/s', $sql, $block)) {
            return;
        }

        preg_match_all(
            "/\((\d+),\s*'[^']*',\s*'[^']*',\s*\d+,\s*[^,]+,\s*[^,]+,\s*\d+,\s*('([^']*)'|NULL)/m",
            $block[0],
            $matches,
            PREG_SET_ORDER
        );

        $fasCount  = 0;
        $venueCount = 0;

        foreach ($matches as $m) {
            $oldVenueId    = (int)$m[1];
            $fasilitasRaw  = $m[2] === 'NULL' ? null : trim($m[3]);
            $prasaranaId   = $this->venueMap[$oldVenueId] ?? null;

            if (!$prasaranaId || !$fasilitasRaw || $fasilitasRaw === '000') continue;

            // Pecah berdasarkan koma
            $items = array_filter(array_map('trim', explode(',', $fasilitasRaw)));
            if (empty($items)) continue;

            // Cek apakah fasilitas sudah ada untuk prasarana ini
            if (FasilitasPrasarana::where('prasarana_id', $prasaranaId)->exists()) continue;

            foreach ($items as $item) {
                if (strlen($item) < 2) continue;
                FasilitasPrasarana::create([
                    'prasarana_id' => $prasaranaId,
                    'nama'         => ucfirst(strtolower($item)),
                    'jumlah'       => 1,
                    'kondisi'      => 'Baik',
                    'keterangan'   => null,
                ]);
                $fasCount++;
            }
            $venueCount++;
        }

        $this->command->info("  -> {$fasCount} fasilitas dari {$venueCount} prasarana berhasil diimport");
    }

    // ── Import Venue Fotos → foto_prasarana ──────────────────────────────────

    private function importFotos(): void
    {
        $sql = file_get_contents($this->sqlFile);

        if (!preg_match('/INSERT INTO `sportif_venue_fotos`[^;]+;/s', $sql, $block)) {
            $this->command->warn('sportif_venue_fotos tidak ditemukan');
            return;
        }

        preg_match_all(
            "/\(\d+,\s*(\d+),\s*'([^']+)'/m",
            $block[0],
            $matches,
            PREG_SET_ORDER
        );

        $count = 0;
        foreach ($matches as $m) {
            [, $oldVenueId, $path] = $m;
            $prasaranaId = $this->venueMap[(int)$oldVenueId] ?? null;
            if (!$prasaranaId) continue;

            // Skip duplikat
            if (FotoPrasarana::where('prasarana_id', $prasaranaId)->where('foto', $path)->exists()) continue;

            FotoPrasarana::create([
                'prasarana_id' => $prasaranaId,
                'foto'         => $path,
                'deskripsi'    => null,
            ]);
            $count++;
        }

        $this->command->info("  -> {$count} foto berhasil diimport");
    }
}
