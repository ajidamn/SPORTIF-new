<?php

namespace Database\Seeders;

use App\Models\Cabor;
use App\Models\KabKota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class KepalaKetuaSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('Sportif2026');

        // ══════════════════════════════════════════════════════════
        //  TINGKAT PROVINSI — 1 akun per role (non-cabor)
        // ══════════════════════════════════════════════════════════
        $provRoles = [
            ['role' => 'Kepala Dinas Provinsi',           'username' => 'kepala_dinas_prov',  'name' => 'Kepala Dinas Provinsi',       'jenis_id' => null, 'cabor_id' => null],
            ['role' => 'Kepala Bidang Olahraga Prestasi', 'username' => 'kabid_orpres',       'name' => 'Kepala Bidang OR Prestasi',   'jenis_id' => 1,    'cabor_id' => null],
            ['role' => 'Kepala Bidang Olahraga Masyarakat','username' => 'kabid_ormas',       'name' => 'Kepala Bidang OR Masyarakat', 'jenis_id' => 2,    'cabor_id' => null],
            ['role' => 'Kepala Bidang Kepemudaan',        'username' => 'kabid_pemuda',       'name' => 'Kepala Bidang Kepemudaan',    'jenis_id' => 3,    'cabor_id' => null],
            ['role' => 'Kepala Bidang Kepramukaan',       'username' => 'kabid_pramuka',      'name' => 'Kepala Bidang Kepramukaan',   'jenis_id' => 4,    'cabor_id' => null],
            ['role' => 'Ketua Koni Provinsi',             'username' => 'ketua_koni_prov',    'name' => 'Ketua KONI Provinsi',         'jenis_id' => 1,    'cabor_id' => null],
            ['role' => 'Ketua Kormi Provinsi',            'username' => 'ketua_kormi_prov',   'name' => 'Ketua KORMI Provinsi',        'jenis_id' => 2,    'cabor_id' => null],
            ['role' => 'Ketua Kwarda Provinsi',           'username' => 'ketua_kwarda_prov',  'name' => 'Ketua Kwarda Provinsi',       'jenis_id' => 4,    'cabor_id' => null],
            ['role' => 'Ketua NPCI Provinsi',             'username' => 'ketua_npci_prov',    'name' => 'Ketua NPCI Provinsi',         'jenis_id' => 1,    'cabor_id' => null],
        ];

        foreach ($provRoles as $r) {
            $user = User::firstOrCreate(
                ['username' => $r['username']],
                [
                    'name'        => $r['name'],
                    'email'       => $r['username'] . '@sportif.jatimprov.go.id',
                    'password'    => $password,
                    'jenis_id'    => $r['jenis_id'],
                    'cabor_id'    => $r['cabor_id'],
                    'is_active'   => true,
                ]
            );
            $user->syncRoles([$r['role']]);
        }

        // ══════════════════════════════════════════════════════════
        //  TINGKAT PROVINSI — Ketua Pengprov per Cabor
        //  1 akun per cabang olahraga, role: "Ketua Pengprov Cabor"
        // ══════════════════════════════════════════════════════════
        $cabors = Cabor::orderBy('nama')->get();

        foreach ($cabors as $cabor) {
            $cleanCabor = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $cabor->nama));
            $cleanCabor = trim($cleanCabor, '_');

            $username = 'ketua_pengprov_' . $cleanCabor;
            if (strlen($username) > 60) {
                $username = substr($username, 0, 60);
            }

            $user = User::firstOrCreate(
                ['username' => $username],
                [
                    'name'        => 'Ketua Pengprov ' . $cabor->nama,
                    'email'       => $username . '@sportif.jatimprov.go.id',
                    'password'    => $password,
                    'jenis_id'    => 1,    // Olahraga Prestasi
                    'cabor_id'    => $cabor->id,
                    'is_active'   => true,
                ]
            );
            $user->syncRoles(['Ketua Pengprov Cabor']);
        }

        // ══════════════════════════════════════════════════════════
        //  TINGKAT KAB/KOTA — 1 akun per role per kab/kota
        // ══════════════════════════════════════════════════════════
        $kabRoles = [
            ['role' => 'Kepala Dinas Kab/Kota',   'prefix' => 'kepala_dinas',   'label' => 'Kepala Dinas',   'jenis_id' => null],
            ['role' => 'Ketua Koni Kab/Kota',     'prefix' => 'ketua_koni',     'label' => 'Ketua KONI',     'jenis_id' => 1],
            ['role' => 'Ketua Kormi Kab/Kota',    'prefix' => 'ketua_kormi',    'label' => 'Ketua KORMI',    'jenis_id' => 2],
            ['role' => 'Ketua NPCI Kab/Kota',     'prefix' => 'ketua_npci',     'label' => 'Ketua NPCI',     'jenis_id' => 1],
            ['role' => 'Ketua Kwarcab Kab/Kota',  'prefix' => 'ketua_kwarcab',  'label' => 'Ketua Kwarcab',  'jenis_id' => 4],
        ];

        $kabKotas = KabKota::all();

        foreach ($kabRoles as $r) {
            foreach ($kabKotas as $kab) {
                $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $kab->name));
                $cleanName = trim($cleanName, '_');

                $username = $r['prefix'] . '_' . $cleanName;
                if (strlen($username) > 60) {
                    $username = substr($username, 0, 60);
                }

                $user = User::firstOrCreate(
                    ['username' => $username],
                    [
                        'name'        => $r['label'] . ' ' . $kab->name,
                        'email'       => $username . '@sportif.com',
                        'password'    => $password,
                        'kab_kota_id' => $kab->id,
                        'jenis_id'    => $r['jenis_id'],
                        'is_active'   => true,
                    ]
                );
                $user->syncRoles([$r['role']]);
            }
        }

        $this->command->info('✅ Seeder Kepala/Ketua selesai.');
    }
}
