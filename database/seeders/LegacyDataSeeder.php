<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Orang;
use App\Models\OrangStatus;
use App\Models\Event;
use App\Models\Prasarana;
use App\Models\FasilitasPrasarana;
use App\Models\Sarana;
use App\Models\RiwayatEvent;

class LegacyDataSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("Starting Legacy Data Migration...");

        // 1. Import SQL Dump if legacy tables don't exist
        if (!Schema::hasTable('sportif_atlets')) {
            $this->command->error("Tabel legacy 'sportif_atlets' TIDAK DITEMUKAN!");
            $this->command->error("HARAP IMPORT file 'data sportif.sql' SECARA MANUAL melalui phpMyAdmin ke dalam database 'sportif_db' terlebih dahulu sebelum menjalankan seeder ini.");
            $this->command->error("Hal ini diperlukan untuk menghindari error 'MySQL server has gone away' akibat file terlalu besar.");
            return;
        }

        // Get Peran IDs dynamically
        $peranAtlet = DB::table('peran')->where('nama', 'Atlet')->value('id') ?? 1;
        $peranPelatih = DB::table('peran')->where('nama', 'Pelatih')->value('id') ?? 2;
        $peranWasit = DB::table('peran')->where('nama', 'like', '%Wasit%')->value('id') ?? 3;
        $peranInstruktur = DB::table('peran')->where('nama', 'Instruktur')->value('id') ?? 4;
        $jenisPrestasi = 1;

        // ID Mapping Arrays
        $mapAtlet = [];
        $mapPelatih = [];
        $mapWasit = [];
        $mapInstruktur = [];
        $mapEvent = [];

        // Helper to process Orang
        $processOrang = function ($oldData, $peranId, &$mapArray, $sourceTable) use ($jenisPrestasi) {
            $nama = $oldData->name ?? $oldData->nama ?? '';
            if (empty(trim($nama))) return; // Skip empty
            
            // Check if exist by NIK (if valid) or Nama
            $orang = null;
            if (!empty($oldData->nik) && strlen($oldData->nik) >= 10) {
                $orang = Orang::where('nik', $oldData->nik)->first();
            }
            if (!$orang) {
                $orang = Orang::where('nama', $nama)->first();
            }

            if (!$orang) {
                $tgl_lahir = $oldData->birth_date ?? $oldData->tanggal_lahir ?? null;
                $telp = $oldData->kontak ?? null;
                $gender = $oldData->gender ?? $oldData->jenis_kelamin ?? 'L';
                $domisili_id = $oldData->kab_kota_id ?? $oldData->domisili ?? null;

                $orang = Orang::create([
                    'nik' => $oldData->nik ?? null,
                    'nama' => $nama,
                    'tgl_lahir' => $tgl_lahir,
                    'telp' => $telp,
                    'alamat' => $oldData->alamat ?? null,
                    'gender' => $gender,
                    'domisili_id' => $domisili_id,
                    'is_active' => true,
                ]);
            }

            // Map old ID to new Orang ID
            $mapArray[$oldData->id] = $orang->id;

            // Create Status
            OrangStatus::firstOrCreate([
                'orang_id' => $orang->id,
                'jenis_id' => $jenisPrestasi,
                'peran_id' => $peranId,
                'cabor_id' => $oldData->cabor_id ?? null,
            ], [
                'id_sitenor' => $oldData->id_sitenor ?? $oldData->no_wasit_juri ?? null,
                'is_active' => true,
            ]);
        };

        // 2. Process Atlet
        $this->command->info("Migrating Atlets...");
        $atlets = DB::table('sportif_atlets')->get();
        foreach ($atlets as $a) $processOrang($a, $peranAtlet, $mapAtlet, 'atlet');

        // Process Pelatih
        $this->command->info("Migrating Pelatihs...");
        $pelatihs = DB::table('sportif_pelatihs')->get();
        foreach ($pelatihs as $p) $processOrang($p, $peranPelatih, $mapPelatih, 'pelatih');

        // Process Wasit
        $this->command->info("Migrating Wasit...");
        if (Schema::hasTable('sportif_wasit_juris')) {
            $wasits = DB::table('sportif_wasit_juris')->get();
            foreach ($wasits as $w) $processOrang($w, $peranWasit, $mapWasit, 'wasit');
        }

        // Process Instruktur
        $this->command->info("Migrating Instruktur...");
        if (Schema::hasTable('sportif_instrukturs')) {
            $instrukturs = DB::table('sportif_instrukturs')->get();
            foreach ($instrukturs as $i) $processOrang($i, $peranInstruktur, $mapInstruktur, 'instruktur');
        }

        // 3. Process Events (Perlombaan)
        $this->command->info("Migrating Perlombaan to Events...");
        $perlombaans = DB::table('sportif_perlombaans')->get();
        foreach ($perlombaans as $p) {
            $skalaName = ucfirst(strtolower($p->tingkat)); // 'provinsi' -> 'Provinsi'
            $skalaId = DB::table('skala')->where('nama', 'like', "%{$skalaName}%")->value('id') ?? 1;

            $event = Event::create([
                'jenis_id' => $jenisPrestasi,
                'nama' => $p->nama,
                'skala_id' => $skalaId,
                'jenis_event' => $p->jenis,
                'penyelenggara' => $p->penyelenggara,
                'tanggal_mulai' => $p->tanggal_mulai,
                'tanggal_selesai' => $p->tanggal_selesai,
                'status' => 'aktif',
            ]);

            $mapEvent[$p->id] = $event->id;
        }

        // 4. Process Venues (Prasarana & Fasilitas)
        $this->command->info("Migrating Venues to Prasarana...");
        $venues = DB::table('sportif_venues')->get();
        foreach ($venues as $v) {
            $prasarana = Prasarana::create([
                'jenis_id' => $jenisPrestasi,
                'lokasi_id' => $v->kota ?? null, // From dump, kota maps to kab_kota_id
                'nama' => $v->nama,
                'latitude' => $v->latitude,
                'longitude' => $v->longitude,
                'pengelola' => null,
                'narahubung' => null,
                'telp_narahubung' => $v->kontak_pengelola ?? null,
                'alamat' => $v->alamat,
                'kapasitas' => $v->kapasitas,
                'keterangan' => $v->keterangan,
                'kategori' => 'Stadion (Sepak bola, atletik)', // Default
                'standar' => 'Belum di Standarisasi',
            ]);

            // Fasilitas parsing
            if (!empty($v->fasilitas)) {
                $fasis = explode(',', $v->fasilitas);
                foreach ($fasis as $fas) {
                    $namaFas = trim($fas);
                    if (!empty($namaFas)) {
                        FasilitasPrasarana::create([
                            'prasarana_id' => $prasarana->id,
                            'nama' => $namaFas,
                            'jumlah' => 1,
                            'kondisi' => 'baik',
                            'keterangan' => 'Migrasi dari venue lama',
                        ]);
                    }
                }
            }
        }

        // 5. Process Sarana
        $this->command->info("Migrating Sarana...");
        if (Schema::hasTable('sportif_saranas')) {
            $saranas = DB::table('sportif_saranas')->get();
            foreach ($saranas as $s) {
                Sarana::create([
                    'jenis_id' => $jenisPrestasi,
                    'kab_kota_id' => $s->kab_kota_id ?? null,
                    'cabor_id' => $s->cabor_id ?? null,
                    'nama_barang' => $s->nama_barang ?? $s->nama ?? 'Sarana Tanpa Nama',
                    'kode_inventaris' => $s->kode_inventaris ?? null,
                    'kondisi' => 'baik',
                    'status' => 'tersedia',
                    'jumlah' => $s->jumlah ?? 1,
                    'satuan' => $s->satuan ?? 'unit',
                    'posisi_aset' => 'internal_dinas',
                ]);
            }
        }

        // 6. Process Riwayat Event (Riwayat Perlombaan)
        $this->command->info("Migrating Riwayat Perlombaan...");
        if (Schema::hasTable('sportif_riwayat_perlombaans')) {
            $riwayats = DB::table('sportif_riwayat_perlombaans')->get();
            foreach ($riwayats as $r) {
                $eventId = $mapEvent[$r->perlombaan_id] ?? null;
                $orangId = $mapAtlet[$r->atlet_id] ?? null;
                
                if (!$eventId || !$orangId) continue;

                $pelatihId = $mapPelatih[$r->pelatih_id] ?? null;
                $wasitId = $mapWasit[$r->wasit_juri_id] ?? null;

                RiwayatEvent::create([
                    'event_id' => $eventId,
                    'cabor_id' => $r->cabor_id,
                    'orang_id' => $orangId,
                    'pelatih_id' => $pelatihId,
                    'wasit_id' => $wasitId,
                    'kategori' => $r->kategori,
                    'prestasi' => $r->prestasi ?? 'peserta',
                    'medali' => (empty($r->medali) || $r->medali === 'none') ? '-' : $r->medali,
                    'tanggal' => $r->tanggal,
                    'keterangan' => $r->keterangan,
                ]);
            }
        }

        // 7. Cleanup Legacy Tables
        $this->command->info("Cleaning up legacy tables...");
        Schema::dropIfExists('sportif_atlets');
        Schema::dropIfExists('sportif_pelatihs');
        Schema::dropIfExists('sportif_wasit_juris');
        Schema::dropIfExists('sportif_instrukturs');
        Schema::dropIfExists('sportif_perlombaans');
        Schema::dropIfExists('sportif_venues');
        Schema::dropIfExists('sportif_saranas');
        Schema::dropIfExists('sportif_riwayat_perlombaans');
        // Drop any other unused tables if necessary
        // Schema::dropIfExists('sportif_detail_atlets');

        $this->command->info("Legacy Data Migration & Cleanup Completed Successfully!");
    }
}
