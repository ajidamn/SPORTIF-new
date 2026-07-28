<?php

namespace Database\Seeders;

use App\Models\Cabor;
use App\Models\Jenis;
use App\Models\KabKota;
use App\Models\Orang;
use App\Models\OrangStatus;
use App\Models\Peran;
use App\Models\Skala;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Jenis ───────────────────────────────────────────────
        $jenisList = [
            'Olahraga Prestasi',
            'Olahraga Masyarakat',
            'Kepemudaan',
            'Kepramukaan',
        ];
        foreach ($jenisList as $j) {
            Jenis::create(['nama' => $j]);
        }

        // ── Peran per Jenis ──────────────────────────────────────
        $peranMap = [
            'Olahraga Prestasi' => ['Atlet', 'Pelatih', 'Instruktur', 'Wasit/Juri', 'Official'],
            'Olahraga Masyarakat' => ['Atlet', 'Pelatih', 'Instruktur', 'Wasit/Juri', 'Official', 'Tenaga Pendukung'],
            'Kepemudaan' => ['Anggota', 'Pengurus'],
            'Kepramukaan' => ['Anggota', 'Pembina', 'Pelatih Pembina', 'Pamong Satuan Karya', 'Instruktur Saka'],
        ];
        foreach ($peranMap as $jenisNama => $peranList) {
            $jenis = Jenis::where('nama', $jenisNama)->first();
            foreach ($peranList as $p) {
                Peran::create(['jenis_id' => $jenis->id, 'nama' => $p]);
            }
        }

        // ── Skala ─────────────────────────────────────────────────
        foreach (['Daerah', 'Provinsi', 'Nasional', 'Internasional'] as $s) {
            Skala::create(['nama' => $s]);
        }

        // ── Kab/Kota Jawa Timur (38 kab/kota dari backup) ────────
        $kabKota = [
            ['name' => 'Kabupaten Pacitan',    'code' => '3501', 'type' => 'kabupaten', 'latitude' => -8.13310000, 'longitude' => 111.16100000],
            ['name' => 'Kabupaten Ponorogo',   'code' => '3502', 'type' => 'kabupaten', 'latitude' => -7.98290000, 'longitude' => 111.49450000],
            ['name' => 'Kabupaten Trenggalek', 'code' => '3503', 'type' => 'kabupaten', 'latitude' => -8.16360000, 'longitude' => 111.61110000],
            ['name' => 'Kabupaten Tulungagung','code' => '3504', 'type' => 'kabupaten', 'latitude' => -8.12560000, 'longitude' => 111.91670000],
            ['name' => 'Kabupaten Blitar',     'code' => '3505', 'type' => 'kabupaten', 'latitude' => -8.13110000, 'longitude' => 112.17010000],
            ['name' => 'Kabupaten Kediri',     'code' => '3506', 'type' => 'kabupaten', 'latitude' => -7.84800000, 'longitude' => 112.01780000],
            ['name' => 'Kabupaten Malang',     'code' => '3507', 'type' => 'kabupaten', 'latitude' => -8.13140000, 'longitude' => 112.55390000],
            ['name' => 'Kabupaten Lumajang',   'code' => '3508', 'type' => 'kabupaten', 'latitude' => -8.08330000, 'longitude' => 113.15570000],
            ['name' => 'Kabupaten Jember',     'code' => '3509', 'type' => 'kabupaten', 'latitude' => -8.23120000, 'longitude' => 113.63940000],
            ['name' => 'Kabupaten Banyuwangi', 'code' => '3510', 'type' => 'kabupaten', 'latitude' => -8.32420000, 'longitude' => 114.21200000],
            ['name' => 'Kabupaten Bondowoso',  'code' => '3511', 'type' => 'kabupaten', 'latitude' => -7.94050000, 'longitude' => 113.88200000],
            ['name' => 'Kabupaten Situbondo',  'code' => '3512', 'type' => 'kabupaten', 'latitude' => -7.77130000, 'longitude' => 114.07290000],
            ['name' => 'Kabupaten Probolinggo','code' => '3513', 'type' => 'kabupaten', 'latitude' => -7.86310000, 'longitude' => 113.34000000],
            ['name' => 'Kabupaten Pasuruan',   'code' => '3514', 'type' => 'kabupaten', 'latitude' => -7.74790000, 'longitude' => 112.86300000],
            ['name' => 'Kabupaten Sidoarjo',   'code' => '3515', 'type' => 'kabupaten', 'latitude' => -7.44670000, 'longitude' => 112.71800000],
            ['name' => 'Kabupaten Mojokerto',  'code' => '3516', 'type' => 'kabupaten', 'latitude' => -7.50280000, 'longitude' => 112.51860000],
            ['name' => 'Kabupaten Jombang',    'code' => '3517', 'type' => 'kabupaten', 'latitude' => -7.54630000, 'longitude' => 112.24710000],
            ['name' => 'Kabupaten Nganjuk',    'code' => '3518', 'type' => 'kabupaten', 'latitude' => -7.59440000, 'longitude' => 111.95670000],
            ['name' => 'Kabupaten Madiun',     'code' => '3519', 'type' => 'kabupaten', 'latitude' => -7.60490000, 'longitude' => 111.65750000],
            ['name' => 'Kabupaten Magetan',    'code' => '3520', 'type' => 'kabupaten', 'latitude' => -7.64370000, 'longitude' => 111.37000000],
            ['name' => 'Kabupaten Ngawi',      'code' => '3521', 'type' => 'kabupaten', 'latitude' => -7.42770000, 'longitude' => 111.35910000],
            ['name' => 'Kabupaten Bojonegoro', 'code' => '3522', 'type' => 'kabupaten', 'latitude' => -7.16010000, 'longitude' => 111.77700000],
            ['name' => 'Kabupaten Tuban',      'code' => '3523', 'type' => 'kabupaten', 'latitude' => -7.02320000, 'longitude' => 111.92130000],
            ['name' => 'Kabupaten Lamongan',   'code' => '3524', 'type' => 'kabupaten', 'latitude' => -7.09340000, 'longitude' => 112.35160000],
            ['name' => 'Kabupaten Gresik',     'code' => '3525', 'type' => 'kabupaten', 'latitude' => -7.03470000, 'longitude' => 112.57140000],
            ['name' => 'Kabupaten Bangkalan',  'code' => '3526', 'type' => 'kabupaten', 'latitude' => -7.06010000, 'longitude' => 112.83510000],
            ['name' => 'Kabupaten Sampang',    'code' => '3527', 'type' => 'kabupaten', 'latitude' => -7.12740000, 'longitude' => 113.30000000],
            ['name' => 'Kabupaten Pamekasan',  'code' => '3528', 'type' => 'kabupaten', 'latitude' => -7.11670000, 'longitude' => 113.50000000],
            ['name' => 'Kabupaten Sumenep',    'code' => '3529', 'type' => 'kabupaten', 'latitude' => -7.01610000, 'longitude' => 114.18640000],
            ['name' => 'Kota Kediri',          'code' => '3571', 'type' => 'kota',      'latitude' => -7.82280000, 'longitude' => 112.01190000],
            ['name' => 'Kota Blitar',          'code' => '3572', 'type' => 'kota',      'latitude' => -8.10200000, 'longitude' => 112.16280000],
            ['name' => 'Kota Malang',          'code' => '3573', 'type' => 'kota',      'latitude' => -7.98190000, 'longitude' => 112.62650000],
            ['name' => 'Kota Probolinggo',     'code' => '3574', 'type' => 'kota',      'latitude' => -7.75690000, 'longitude' => 113.21610000],
            ['name' => 'Kota Pasuruan',        'code' => '3575', 'type' => 'kota',      'latitude' => -7.64440000, 'longitude' => 112.90670000],
            ['name' => 'Kota Mojokerto',       'code' => '3576', 'type' => 'kota',      'latitude' => -7.47260000, 'longitude' => 112.43820000],
            ['name' => 'Kota Madiun',          'code' => '3577', 'type' => 'kota',      'latitude' => -7.62980000, 'longitude' => 111.52990000],
            ['name' => 'Kota Surabaya',        'code' => '3578', 'type' => 'kota',      'latitude' => -7.25750000, 'longitude' => 112.75210000],
            ['name' => 'Kota Batu',            'code' => '3579', 'type' => 'kota',      'latitude' => -7.87110000, 'longitude' => 112.52680000],
        ];
        foreach ($kabKota as $kk) {
            KabKota::create($kk);
        }

        // ── Cabor Olahraga Prestasi (KONI) — 73 cabor dari backup ──
        // Semua cabor di KONI bersifat "olahraga_prestasi"
        // Cabor olahraga masyarakat (KORMI) umumnya non-kompetitif
        $caborPrestasi = [
            ['nama' => 'Aeromodelling',           'nama_pengprov' => 'FASI Jawa Timur',          'keterangan' => 'Federasi Aero Sport Indonesia'],
            ['nama' => 'Akuatik (Renang, Loncat Indah)', 'nama_pengprov' => 'Akuatik Indonesia Jawa Timur', 'keterangan' => 'Akuatik Indonesia (dahulu PRSI)'],
            ['nama' => 'Anggar',                  'nama_pengprov' => 'IKASI Jawa Timur',         'keterangan' => 'Ikatan Anggar Seluruh Indonesia'],
            ['nama' => 'Angkat Besi',             'nama_pengprov' => 'PABSI Jawa Timur',         'keterangan' => 'Persatuan Angkat Besi Seluruh Indonesia'],
            ['nama' => 'Angkat Berat',            'nama_pengprov' => 'PABERSI Jawa Timur',       'keterangan' => 'Perkumpulan Angkat Berat Seluruh Indonesia'],
            ['nama' => 'Arung Jeram',             'nama_pengprov' => 'FAJI Jawa Timur',          'keterangan' => 'Federasi Arung Jeram Indonesia'],
            ['nama' => 'Atletik',                 'nama_pengprov' => 'PASI Jawa Timur',          'keterangan' => 'Persatuan Atletik Seluruh Indonesia'],
            ['nama' => 'Balap Sepeda',            'nama_pengprov' => 'ISSI Jawa Timur',          'keterangan' => 'Ikatan Sport Sepeda Indonesia'],
            ['nama' => 'Barongsai',               'nama_pengprov' => 'FOBI Jawa Timur',          'keterangan' => 'Federasi Olahraga Barongsai Indonesia'],
            ['nama' => 'Berkuda',                 'nama_pengprov' => 'PORDASI Jawa Timur',       'keterangan' => 'Persatuan Olahraga Berkuda Seluruh Indonesia'],
            ['nama' => 'Biliar',                  'nama_pengprov' => 'POBSI Jawa Timur',         'keterangan' => 'Persatuan Olahraga Biliar Seluruh Indonesia'],
            ['nama' => 'Binaraga',                'nama_pengprov' => 'PBFI Jawa Timur',          'keterangan' => 'Perkumpulan Binaraga Fitnes Indonesia'],
            ['nama' => 'Bola Basket',             'nama_pengprov' => 'PERBASI Jawa Timur',       'keterangan' => 'Persatuan Bola Basket Seluruh Indonesia'],
            ['nama' => 'Bola Tangan',             'nama_pengprov' => 'ABTI Jawa Timur',          'keterangan' => 'Asosiasi Bola Tangan Indonesia'],
            ['nama' => 'Bola Voli',               'nama_pengprov' => 'PBVSI Jawa Timur',         'keterangan' => 'Persatuan Bola Voli Seluruh Indonesia'],
            ['nama' => 'Bowling',                 'nama_pengprov' => 'PBI Jawa Timur',           'keterangan' => 'Persatuan Bowling Indonesia'],
            ['nama' => 'Bridge',                  'nama_pengprov' => 'GABSI Jawa Timur',         'keterangan' => 'Gabungan Bridge Seluruh Indonesia'],
            ['nama' => 'Bulu Tangkis',            'nama_pengprov' => 'PBSI Jawa Timur',          'keterangan' => 'Persatuan Bulu Tangkis Seluruh Indonesia'],
            ['nama' => 'Catur',                   'nama_pengprov' => 'PERCASI Jawa Timur',       'keterangan' => 'Persatuan Catur Seluruh Indonesia'],
            ['nama' => 'Cricket',                 'nama_pengprov' => 'PCI Jawa Timur',           'keterangan' => 'Persatuan Cricket Indonesia'],
            ['nama' => 'Dansa',                   'nama_pengprov' => 'IODI Jawa Timur',          'keterangan' => 'Ikatan Olahraga Dansa Indonesia'],
            ['nama' => 'Dayung',                  'nama_pengprov' => 'PODSI Jawa Timur',         'keterangan' => 'Persatuan Olahraga Dayung Seluruh Indonesia'],
            ['nama' => 'Drumband',                'nama_pengprov' => 'PDBI Jawa Timur',          'keterangan' => 'Persatuan Drum Band Indonesia'],
            ['nama' => 'E-Sport',                 'nama_pengprov' => 'ESI Jawa Timur',           'keterangan' => 'Elektronik Sport Indonesia'],
            ['nama' => 'Gateball',                'nama_pengprov' => 'PERGATSI Jawa Timur',      'keterangan' => 'Persatuan Gateball Seluruh Indonesia'],
            ['nama' => 'Golf',                    'nama_pengprov' => 'PGI Jawa Timur',           'keterangan' => 'Persatuan Golf Indonesia'],
            ['nama' => 'Gulat',                   'nama_pengprov' => 'PGSI Jawa Timur',          'keterangan' => 'Persatuan Gulat Seluruh Indonesia'],
            ['nama' => 'Hapkido',                 'nama_pengprov' => 'HI Jawa Timur',            'keterangan' => 'Hapkido Indonesia'],
            ['nama' => 'Hockey',                  'nama_pengprov' => 'FHI Jawa Timur',           'keterangan' => 'Federasi Hockey Indonesia'],
            ['nama' => 'Judo',                    'nama_pengprov' => 'PJSI Jawa Timur',          'keterangan' => 'Persatuan Judo Seluruh Indonesia'],
            ['nama' => 'Jujitsu',                 'nama_pengprov' => 'PBJI Jawa Timur',          'keterangan' => 'Pengurus Besar Jujitsu Indonesia'],
            ['nama' => 'Kabaddi',                 'nama_pengprov' => 'FKI Jawa Timur',           'keterangan' => 'Federasi Kabaddi Indonesia'],
            ['nama' => 'Karate',                  'nama_pengprov' => 'FORKI Jawa Timur',         'keterangan' => 'Federasi Olahraga Karate-Do Indonesia'],
            ['nama' => 'Kempo',                   'nama_pengprov' => 'PERKEMI Jawa Timur',       'keterangan' => 'Persaudaraan Shorinji Kempo Indonesia'],
            ['nama' => 'Kick Boxing',             'nama_pengprov' => 'KBI Jawa Timur',           'keterangan' => 'Kick Boxing Indonesia'],
            ['nama' => 'Kurash',                  'nama_pengprov' => 'FERKUSHI Jawa Timur',      'keterangan' => 'Federasi Kurash Indonesia'],
            ['nama' => 'Layar',                   'nama_pengprov' => 'PORLASI Jawa Timur',       'keterangan' => 'Persatuan Olahraga Layar Seluruh Indonesia'],
            ['nama' => 'Menembak',                'nama_pengprov' => 'PERBAKIN Jawa Timur',      'keterangan' => 'Persatuan Menembak dan Berburu Indonesia'],
            ['nama' => 'Modern Pentathlon',       'nama_pengprov' => 'MPI Jawa Timur',           'keterangan' => 'Modern Pentathlon Indonesia'],
            ['nama' => 'Muaythai',                'nama_pengprov' => 'MI Jawa Timur',            'keterangan' => 'Muaythai Indonesia'],
            ['nama' => 'Panahan',                 'nama_pengprov' => 'PERPANI Jawa Timur',       'keterangan' => 'Persatuan Panahan Indonesia'],
            ['nama' => 'Panjat Tebing',           'nama_pengprov' => 'FPTI Jawa Timur',          'keterangan' => 'Federasi Panjat Tebing Indonesia'],
            ['nama' => 'Paralayang',              'nama_pengprov' => 'FASI Jawa Timur',          'keterangan' => 'Federasi Aero Sport Indonesia'],
            ['nama' => 'Paramotor',               'nama_pengprov' => 'FASI Jawa Timur',          'keterangan' => 'Federasi Aero Sport Indonesia'],
            ['nama' => 'Pencak Silat',            'nama_pengprov' => 'IPSI Jawa Timur',          'keterangan' => 'Ikatan Pencak Silat Indonesia'],
            ['nama' => 'Petanque',                'nama_pengprov' => 'FOPI Jawa Timur',          'keterangan' => 'Federasi Olahraga Petanque Indonesia'],
            ['nama' => 'Pickleball',              'nama_pengprov' => 'IPFI Jawa Timur',          'keterangan' => 'Indonesia Pickleball Federation Indonesia'],
            ['nama' => 'Sambo',                   'nama_pengprov' => 'PERSAMBI Jawa Timur',      'keterangan' => 'Persatuan Sambo Indonesia'],
            ['nama' => 'Selam',                   'nama_pengprov' => 'POSSI Jawa Timur',         'keterangan' => 'Persatuan Olahraga Selam Seluruh Indonesia'],
            ['nama' => 'Selancar Ombak',          'nama_pengprov' => 'PSOI Jawa Timur',          'keterangan' => 'Persatuan Selancar Ombak Indonesia'],
            ['nama' => 'Senam',                   'nama_pengprov' => 'PERSANI Jawa Timur',       'keterangan' => 'Persatuan Senam Indonesia'],
            ['nama' => 'Sepak Bola',              'nama_pengprov' => 'PSSI Jawa Timur',          'keterangan' => 'Persatuan Sepak Bola Seluruh Indonesia'],
            ['nama' => 'Sepak Takraw',            'nama_pengprov' => 'PSTI Jawa Timur',          'keterangan' => 'Persatuan Sepak Takraw Seluruh Indonesia'],
            ['nama' => 'Sepatu Roda',             'nama_pengprov' => 'PERSEROSI Jawa Timur',     'keterangan' => 'Persatuan Olahraga Sepatu Roda Seluruh Indonesia'],
            ['nama' => 'Shorinji Kempo',          'nama_pengprov' => 'PERKEMI Jawa Timur',       'keterangan' => 'Persaudaraan Shorinji Kempo Indonesia'],
            ['nama' => 'Soft Tennis',             'nama_pengprov' => 'PESTI Jawa Timur',         'keterangan' => 'Persatuan Soft Tennis Indonesia'],
            ['nama' => 'Squash',                  'nama_pengprov' => 'PSI Jawa Timur',           'keterangan' => 'Persatuan Squash Indonesia'],
            ['nama' => 'Taekwondo',               'nama_pengprov' => 'TI Jawa Timur',            'keterangan' => 'Taekwondo Indonesia'],
            ['nama' => 'Tarung Derajat',          'nama_pengprov' => 'KODRAT Jawa Timur',        'keterangan' => 'Keluarga Olahraga Tarung Derajat'],
            ['nama' => 'Tenis Lapangan',          'nama_pengprov' => 'PELTI Jawa Timur',         'keterangan' => 'Persatuan Tennis Seluruh Indonesia'],
            ['nama' => 'Tenis Meja',              'nama_pengprov' => 'PTMSI Jawa Timur',         'keterangan' => 'Persatuan Tenis Meja Seluruh Indonesia'],
            ['nama' => 'Tinju',                   'nama_pengprov' => 'PERTINA Jawa Timur',       'keterangan' => 'Persatuan Tinju Amatir Indonesia'],
            ['nama' => 'Triathlon',               'nama_pengprov' => 'FTI Jawa Timur',           'keterangan' => 'Federasi Triathlon Indonesia'],
            ['nama' => 'Woodball',                'nama_pengprov' => 'IWbA Jawa Timur',          'keterangan' => 'Indonesia Woodball Association'],
            ['nama' => 'Wushu',                   'nama_pengprov' => 'WI Jawa Timur',            'keterangan' => 'Wushu Indonesia'],
            ['nama' => 'Rugby',                   'nama_pengprov' => 'PRUI Jawa Timur',          'keterangan' => 'Persatuan Rugby Union Indonesia'],
            ['nama' => 'Futsal',                  'nama_pengprov' => 'Asosiasi Futsal Provinsi (AFP) Jawa Timur', 'keterangan' => null],
            ['nama' => 'BMX',                     'nama_pengprov' => 'ISSI Jawa Timur',          'keterangan' => null],
            ['nama' => 'Bermotor',                'nama_pengprov' => 'IMI Jawa Timur',           'keterangan' => null],
            ['nama' => 'Softball & Baseball',     'nama_pengprov' => 'Perbasasi Jawa Timur',     'keterangan' => null],
            ['nama' => 'Gantole',                 'nama_pengprov' => 'FASI Jawa Timur',          'keterangan' => null],
            ['nama' => 'MMA',                     'nama_pengprov' => 'IBCA MMA Jawa Timur',      'keterangan' => null],
            ['nama' => 'Ski Air',                 'nama_pengprov' => 'PSAWI Jawa Timur',         'keterangan' => null],
        ];

        foreach ($caborPrestasi as $c) {
            Cabor::create(array_merge($c, ['tipe' => 'olahraga_prestasi']));
        }

        // ── Cabor Olahraga Masyarakat (KORMI) ────────────────────
        // Olahraga rekreasi/masyarakat yang umumnya non-kompetitif
        $caborMasyarakat = [
            ['nama' => 'Senam Aerobik',        'nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Senam aerobik dan fitnes masyarakat'],
            ['nama' => 'Jalan Santai',         'nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Olahraga jalan santai massal'],
            ['nama' => 'Tenis Meja Rekreasi',  'nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Tenis meja untuk masyarakat'],
            ['nama' => 'Bola Voli Pantai',     'nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Voli pantai masyarakat'],
            ['nama' => 'Sepak Takraw Rekreasi','nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Sepak takraw masyarakat'],
            ['nama' => 'Bulutangkis Rekreasi', 'nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Bulutangkis masyarakat'],
            ['nama' => 'Renang Masyarakat',    'nama_pengprov' => 'KORMI Jawa Timur', 'keterangan' => 'Renang untuk masyarakat umum'],
        ];

        foreach ($caborMasyarakat as $c) {
            Cabor::create(array_merge($c, ['tipe' => 'olahraga_masyarakat']));
        }

        // ── Roles (Spatie Permission) — 32 Role ────────────────
        $roles = [
            // Tingkat Provinsi
            'SuperAdmin',
            'Kepala Dinas Provinsi',
            'Admin Dispora Provinsi',
            'Kepala Bidang Olahraga Prestasi',
            'Kepala Bidang Olahraga Masyarakat',
            'Kepala Bidang Kepemudaan',
            'Kepala Bidang Kepramukaan',
            'Ketua Koni Provinsi',
            'Ketua Kormi Provinsi',
            'Ketua Kwarda Provinsi',
            'Ketua NPCI Provinsi',
            'Ketua Pengprov Cabor',
            'Ketua Inorga Provinsi',
            'Admin Koni Provinsi',
            'Admin Kormi Provinsi',
            'Admin NPCI Provinsi',
            'Admin Inorga Provinsi',
            'Admin Bidang Provinsi',
            'Admin Pengprov',
            'Admin Kwarda',

            // Tingkat Kab/Kota
            'Kepala Dinas Kab/Kota',
            'Admin Dispora Kab/Kota',
            'Ketua Koni Kab/Kota',
            'Ketua NPCI Kab/Kota',
            'Ketua Kormi Kab/Kota',
            'Ketua Kwarcab Kab/Kota',
            'Ketua Pengcab Cabor',
            'Ketua Inorga Kab/Kota',
            'Admin Koni Kab/Kota',
            'Admin Inorga Kab/Kota',
            'Admin Kwarcab',
            'Admin OKP',
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // ── SuperAdmin User ────────────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'superadmin@sportif.jatimprov.go.id'],
            [
                'name'      => 'Super Admin',
                'username'  => 'superadmin',
                'password'  => Hash::make('sportif2026'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('SuperAdmin');

        // ── Admin Dispora Provinsi (4 Akun) ────────────────────────
        for ($i = 1; $i <= 4; $i++) {
            $provAdmin = User::firstOrCreate(
                ['email' => "admin_prov{$i}@sportif.jatimprov.go.id"],
                [
                    'name'      => "Admin Dispora Provinsi {$i}",
                    'username'  => "admin_prov{$i}",
                    'password'  => Hash::make('sportif2026'),
                    'is_active' => true,
                ]
            );
            $provAdmin->assignRole('Admin Dispora Provinsi');
        }

        // ── Admin Dispora Kab/Kota (38 Akun) ───────────────────────
        $kabKotas = KabKota::all();
        foreach ($kabKotas as $kab) {
            // Clean up name for username & email
            $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $kab->name));
            $cleanName = trim($cleanName, '_');
            
            $kabAdmin = User::firstOrCreate(
                ['email' => "admin_dispora_{$cleanName}@sportif.com"],
                [
                    'name'        => "Admin Dispora " . $kab->name,
                    'username'    => "admin_dispora_{$cleanName}",
                    'password'    => Hash::make('Sportif2026'),
                    'kab_kota_id' => $kab->id,
                    'is_active'   => true,
                ]
            );
            $kabAdmin->assignRole('Admin Dispora Kab/Kota');
        }

        // ── Import Data Legacy dari Backup ────────────────────────
        // Jalankan seeder khusus import (Orang, Event, Venue, Sarana, Riwayat)
        $this->call([
            LegacyDataSeeder::class,
        ]);
    }
}