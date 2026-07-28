<?php

namespace Database\Seeders;

use App\Models\Cabor;
use App\Models\Event;
use App\Models\Jenis;
use App\Models\KabKota;
use App\Models\Orang;
use App\Models\RiwayatEvent;
use App\Models\Skala;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportEventSeeder extends Seeder
{
    private string $sqlPath;

    // Map: old_id → new_id
    private array $skalaMap  = [];
    private array $caborMap  = [];
    private array $orangMap  = [];   // old atlet_id → orang.id
    private int $jenisOlahragaPrestasiId;

    public function run(): void
    {
        $this->sqlPath = base_path('data sportif.sql');
        if (!file_exists($this->sqlPath)) {
            $this->command->error('File backup SQL tidak ditemukan: ' . $this->sqlPath);
            return;
        }

        $sql = file_get_contents($this->sqlPath);
        $this->command->info('Memuat peta referensi event...');
        $this->buildMaps();

        $this->command->info('Mengimport perlombaan dari backup...');
        $this->importPerlombaan($sql);

        $this->command->info('Mengimport riwayat prestasi dari backup...');
        $this->importRiwayat($sql);
    }

    private function buildMaps(): void
    {
        // Skala: backup pakai enum string (internasional, nasional, provinsi, daerah)
        $this->skalaMap = Skala::pluck('id', 'nama')
            ->mapWithKeys(fn($id, $nama) => [strtolower($nama) => $id])
            ->toArray();

        // Tambahkan alias
        $this->skalaMap['internasional'] = $this->skalaMap['internasional'] ?? null;
        $this->skalaMap['nasional']      = $this->skalaMap['nasional']      ?? null;
        $this->skalaMap['provinsi']      = $this->skalaMap['provinsi']      ?? null;
        $this->skalaMap['daerah']        = $this->skalaMap['daerah']        ?? null;

        // Jenis — cari "Olahraga Prestasi" (id=1)
        $jenis = Jenis::where('nama', 'like', '%prestasi%')->first()
               ?? Jenis::first();
        $this->jenisOlahragaPrestasiId = $jenis?->id ?? 1;

        // Cabor map: old_id (dari backup) → new cabor.id
        // Kita load berdasarkan nama karena id mungkin berubah
        // Di backup: sportif_perlombaan_cabor.cabor_id → sportif_cabors.id
        // Kita asumsikan cabor old_id == new id karena seednya sama
        $this->caborMap = Cabor::pluck('id', 'id')->toArray();

        // Orang map: old atlet_id dari backup → orang.id baru
        // Backup: sportif_riwayat_perlombaans.atlet_id → sportif_atlets.id
        // Kita cari orang via old_id yang tersimpan saat import atlet
        // Karena ImportOrangSeeder tidak menyimpan old_id mapping,
        // kita gunakan urutan NIK dari backup untuk rebuild map
        $this->command->info('  Membangun peta orang dari backup atlet...');
        $this->buildOrangMap();
    }

    private function buildOrangMap(): void
    {
        // Baca INSERT sportif_atlets untuk dapatkan (id, nik, nama) → map ke orang.id
        $sqlPath = $this->sqlPath;
        $sql = file_get_contents($sqlPath);

        // Extract sportif_atlets insert block
        if (preg_match('/INSERT INTO `sportif_atlets`[^;]+;/s', $sql, $m)) {
            preg_match_all(
                "/\((\d+),\s*'([^']*)'\s*,\s*'([^']*)'/m",
                $m[0],
                $matches,
                PREG_SET_ORDER
            );
            foreach ($matches as $row) {
                [, $oldId, $nik, $nama] = $row;
                // Cari orang by NIK
                $orang = null;
                if ($nik) {
                    $orang = Orang::where('nik', $nik)->first();
                }
                if (!$orang && $nama) {
                    $orang = Orang::where('nama', $nama)->first();
                }
                if ($orang) {
                    $this->orangMap[(int)$oldId] = $orang->id;
                }
            }
        }
        $this->command->info('  Peta orang: ' . count($this->orangMap) . ' atlet terpetakan');
    }

    private function importPerlombaan(string $sql): void
    {
        // Extract INSERT sportif_perlombaans
        if (!preg_match('/INSERT INTO `sportif_perlombaans`[^;]+;/s', $sql, $block)) {
            $this->command->warn('  -> sportif_perlombaans tidak ditemukan');
            return;
        }

        // (id, nama, tingkat, jenis, penyelenggara, tanggal_mulai, tanggal_selesai, status, ...)
        preg_match_all(
            "/\((\d+),\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*([^,]+),\s*([^,]+),\s*'([^']*)'/m",
            $block[0],
            $matches,
            PREG_SET_ORDER
        );

        $count = 0;
        foreach ($matches as $m) {
            [, $oldId, $nama, $tingkat, $jenis, $penyelenggara, $tglMulai, $tglSelesai, $status] = $m;

            // Skip jika event sudah ada (berdasarkan nama unik)
            if (Event::where('nama', $nama)->exists()) continue;

            $skalaId = $this->skalaMap[$tingkat] ?? null;

            $tglMulai    = trim($tglMulai)   !== 'NULL' ? trim($tglMulai, "'")   : null;
            $tglSelesai  = trim($tglSelesai) !== 'NULL' ? trim($tglSelesai, "'") : null;

            // Map jenis event backup: 'single event'/'multi event' → sesuai ENUM baru
            $jenisEvent = in_array($jenis, ['single event', 'multi event', 'pelatihan', 'perlombaan'])
                ? $jenis : 'perlombaan';

            $event = Event::create([
                'jenis_id'        => $this->jenisOlahragaPrestasiId,
                'nama'            => $nama,
                'skala_id'        => $skalaId,
                'jenis_event'     => $jenisEvent,
                'penyelenggara'   => $penyelenggara,
                'tanggal_mulai'   => $tglMulai,
                'tanggal_selesai' => $tglSelesai,
                'status'          => $status === 'active' ? 'aktif' : 'selesai',
            ]);

            // Pasang cabor dari sportif_perlombaan_cabor
            $this->syncCaborEvent($sql, (int)$oldId, $event->id);
            $count++;
        }

        $this->command->info("  -> {$count} event berhasil diimport.");
        // Simpan mapping old event id → new event id untuk riwayat
        $this->buildEventIdMap($sql);
    }

    // Map: old_perlombaan_id → new events.id
    private array $eventIdMap = [];

    private function buildEventIdMap(string $sql): void
    {
        if (!preg_match('/INSERT INTO `sportif_perlombaans`[^;]+;/s', $sql, $block)) return;

        preg_match_all(
            "/\((\d+),\s*'([^']*)'/m",
            $block[0], $matches, PREG_SET_ORDER
        );

        foreach ($matches as $m) {
            [, $oldId, $nama] = $m;
            $event = Event::where('nama', $nama)->first();
            if ($event) {
                $this->eventIdMap[(int)$oldId] = $event->id;
            }
        }
    }

    private function syncCaborEvent(string $sql, int $oldEventId, int $newEventId): void
    {
        if (!preg_match('/INSERT INTO `sportif_perlombaan_cabor`[^;]+;/s', $sql, $block)) return;

        preg_match_all(
            "/\(\d+,\s*{$oldEventId},\s*(\d+)\)/m",
            $block[0], $matches, PREG_SET_ORDER
        );

        $caborIds = [];
        foreach ($matches as $m) {
            $oldCaborId = (int)$m[1];
            if (isset($this->caborMap[$oldCaborId])) {
                $caborIds[] = $this->caborMap[$oldCaborId];
            }
        }

        if ($caborIds) {
            Event::find($newEventId)?->cabors()->sync($caborIds);
        }
    }

    private function importRiwayat(string $sql): void
    {
        if (!preg_match('/INSERT INTO `sportif_riwayat_perlombaans`[^;]+;/s', $sql, $block)) {
            $this->command->warn('  -> sportif_riwayat_perlombaans tidak ditemukan');
            return;
        }

        // (id, perlombaan_id, cabor_id, atlet_id, pelatih_id, wasit_juri_id, kategori, prestasi, tanggal, medali, keterangan, ...)
        preg_match_all(
            "/\((\d+),\s*(\d+),\s*(\d+),\s*(\d+),\s*([^,]+),\s*([^,]+),\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*'([^']*)'\s*,\s*([^,]+)/m",
            $block[0],
            $matches,
            PREG_SET_ORDER
        );

        $count   = 0;
        $skipped = 0;

        foreach ($matches as $m) {
            [, $id, $oldEventId, $oldCaborId, $oldAtletId, $pelatihRaw, $wasitRaw,
             $kategori, $prestasi, $tanggal, $medali, $keterangan] = $m;

            $newEventId = $this->eventIdMap[(int)$oldEventId] ?? null;
            $newOrangId = $this->orangMap[(int)$oldAtletId]   ?? null;

            // Orang wajib ada
            if (!$newOrangId || !$newEventId) { $skipped++; continue; }

            $newCaborId = isset($this->caborMap[(int)$oldCaborId]) ? $this->caborMap[(int)$oldCaborId] : null;

            // Map medali
            $medaliClean = in_array($medali, ['emas','perak','perunggu','-']) ? $medali : null;

            // Hindari duplikasi
            if (RiwayatEvent::where('event_id', $newEventId)
                ->where('orang_id', $newOrangId)
                ->where('kategori', $kategori)
                ->exists()) continue;

            RiwayatEvent::create([
                'event_id'  => $newEventId,
                'orang_id'  => $newOrangId,
                'cabor_id'  => $newCaborId,
                'kategori'  => $kategori ?: null,
                'prestasi'  => $prestasi ?: null,
                'medali'    => $medaliClean,
                'tanggal'   => $tanggal !== '0000-00-00' ? $tanggal : null,
                'keterangan'=> trim($keterangan, "'") ?: null,
            ]);

            $count++;
        }

        $this->command->info("  -> {$count} riwayat diimport, {$skipped} dilewati (orang/event tidak ditemukan).");
    }
}
