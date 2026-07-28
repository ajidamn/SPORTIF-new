<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;

/*
|--------------------------------------------------------------------------
| API Routes — SPORTIF DISPORA JATIM
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['auth:sanctum,web', 'throttle:60,1'])->group(function () {

    // ══════════════════════════════════════════════════════════
    //  PROFIL — Semua user bisa update profil sendiri
    // ══════════════════════════════════════════════════════════
    Route::put('profile/email', [Api\ProfileController::class, 'updateEmail']);

    // ══════════════════════════════════════════════════════════
    //  SEMUA ROLE — READ (GET) diizinkan untuk semua
    //  CUD (POST/PUT/DELETE) dijaga oleh middleware 'readonly'
    // ══════════════════════════════════════════════════════════

    // ── Data Orang ──────────────────────────────────────────
    Route::get('orang', [Api\OrangController::class, 'index']);
    Route::get('orang/{orang}', [Api\OrangController::class, 'show'])->middleware('log-sensitive');
    Route::middleware('readonly')->group(function () {
        Route::post('orang', [Api\OrangController::class, 'store']);
        Route::put('orang/{orang}', [Api\OrangController::class, 'update']);
        Route::delete('orang/{orang}', [Api\OrangController::class, 'destroy']);
    });

    // Orang Status (multi-status per orang)
    Route::get('orang/{orang}/status', [Api\OrangStatusController::class, 'index']);
    Route::middleware('readonly')->prefix('orang/{orang}/status')->group(function () {
        Route::post('/', [Api\OrangStatusController::class, 'store']);
        Route::post('/batch', [Api\OrangStatusController::class, 'batch']);
        Route::put('/{status}', [Api\OrangStatusController::class, 'update']);
        Route::delete('/{status}', [Api\OrangStatusController::class, 'destroy']);
    });

    // Orang Riwayat Event
    Route::get('orang/{orang}/riwayat', [Api\RiwayatEventController::class, 'indexByOrang']);
    Route::post('orang/{orang}/riwayat', [Api\RiwayatEventController::class, 'storeForOrang'])->middleware('readonly');

    // ── Event ────────────────────────────────────────────────
    Route::get('events', [Api\EventController::class, 'index']);
    Route::get('events/{event}', [Api\EventController::class, 'show']);
    Route::get('events/cabor-by-jenis/{jenisId}', [Api\EventController::class, 'caborByJenis']);
    Route::get('events/{event}/riwayat', [Api\EventController::class, 'riwayat']);
    Route::middleware('readonly')->group(function () {
        Route::post('events', [Api\EventController::class, 'store']);
        Route::put('events/{event}', [Api\EventController::class, 'update']);
        Route::delete('events/{event}', [Api\EventController::class, 'destroy']);
        Route::post('events/{event}/riwayat/import', [Api\EventController::class, 'importRiwayat']);
    });

    // Riwayat Event CRUD
    Route::middleware('readonly')->group(function () {
        Route::put('riwayat-event/{riwayatEvent}', [Api\RiwayatEventController::class, 'update']);
        Route::delete('riwayat-event/{riwayatEvent}', [Api\RiwayatEventController::class, 'destroy']);
    });

    // Autocomplete cari orang
    Route::get('cari-orang', [Api\RiwayatEventController::class, 'cariOrang']);

    // ── Prasarana ────────────────────────────────────────────
    Route::get('prasarana', [Api\PrasaranaController::class, 'index']);
    Route::get('prasarana/{prasarana}', [Api\PrasaranaController::class, 'show']);
    Route::middleware('readonly')->group(function () {
        Route::post('prasarana', [Api\PrasaranaController::class, 'store']);
        Route::put('prasarana/{prasarana}', [Api\PrasaranaController::class, 'update']);
        Route::delete('prasarana/{prasarana}', [Api\PrasaranaController::class, 'destroy']);
        Route::post('prasarana/{prasarana}/fasilitas', [Api\PrasaranaController::class, 'storeFasilitas']);
        Route::put('prasarana/{prasarana}/fasilitas/{fasilitas}', [Api\PrasaranaController::class, 'updateFasilitas']);
        Route::delete('prasarana/{prasarana}/fasilitas/{fasilitas}', [Api\PrasaranaController::class, 'destroyFasilitas']);
        Route::post('prasarana/{prasarana}/foto', [Api\PrasaranaController::class, 'uploadFoto']);
        Route::delete('prasarana/foto/{foto}', [Api\PrasaranaController::class, 'destroyFoto']);
    });

    // ── Sarana ────────────────────────────────────────────────
    Route::get('sarana', [Api\SaranaController::class, 'index']);
    Route::get('sarana/{sarana}', [Api\SaranaController::class, 'show']);
    Route::middleware('readonly')->group(function () {
        Route::post('sarana', [Api\SaranaController::class, 'store']);
        Route::put('sarana/{sarana}', [Api\SaranaController::class, 'update']);
        Route::delete('sarana/{sarana}', [Api\SaranaController::class, 'destroy']);
    });

    // ── Organisasi ───────────────────────────────────────────
    Route::get('organisasi', [Api\OrganisasiController::class, 'index']);
    Route::get('organisasi/{organisasi}', [Api\OrganisasiController::class, 'show']);
    Route::middleware('readonly')->group(function () {
        Route::post('organisasi', [Api\OrganisasiController::class, 'store']);
        Route::put('organisasi/{organisasi}', [Api\OrganisasiController::class, 'update']);
        Route::delete('organisasi/{organisasi}', [Api\OrganisasiController::class, 'destroy']);
    });

    // ══════════════════════════════════════════════════════════
    //  EKSTRAKURIKULER SEKOLAH — Domain jenis_id = 2
    //  Akses: Kepala Dinas Prov, Kepala Bidang (jenis=2),
    //         Admin Dispora Prov (jenis=2), Kepala Dispora Kab/Kota,
    //         Admin Dispora Kab/Kota, SuperAdmin
    // ══════════════════════════════════════════════════════════

    // Master Jenis Ekstrakurikuler (GET terbuka untuk dropdown)
    Route::get('jenis-ekstrakurikuler', [Api\EkstrakurikulerController::class, 'jenisIndex']);
    Route::get('jenis-ekstrakurikuler/{jenisEkstrakurikuler}', [Api\EkstrakurikulerController::class, 'jenisShow']);
    Route::middleware('role:SuperAdmin|Admin Dispora Provinsi')->group(function () {
        Route::post('jenis-ekstrakurikuler', [Api\EkstrakurikulerController::class, 'jenisStore']);
        Route::put('jenis-ekstrakurikuler/{jenisEkstrakurikuler}', [Api\EkstrakurikulerController::class, 'jenisUpdate']);
        Route::delete('jenis-ekstrakurikuler/{jenisEkstrakurikuler}', [Api\EkstrakurikulerController::class, 'jenisDestroy']);
    });

    // Data Sekolah
    Route::get('sekolah', [Api\EkstrakurikulerController::class, 'sekolahIndex']);
    Route::get('sekolah/{sekolah}', [Api\EkstrakurikulerController::class, 'sekolahShow']);
    Route::middleware('readonly')->group(function () {
        Route::post('sekolah', [Api\EkstrakurikulerController::class, 'sekolahStore']);
        Route::put('sekolah/{sekolah}', [Api\EkstrakurikulerController::class, 'sekolahUpdate']);
        Route::delete('sekolah/{sekolah}', [Api\EkstrakurikulerController::class, 'sekolahDestroy']);
    });

    // Data Ekstrakurikuler per Sekolah
    // ══════════════════════════════════════════════════════════
    //  DASHBOARD EKSEKUTIF API
    // ══════════════════════════════════════════════════════════
    Route::get('dashboard/stats', [Api\DashboardApiController::class, 'stats']);
    Route::get('dashboard/prestasi', [Api\DashboardApiController::class, 'prestasi']);
    Route::get('dashboard/charts', [Api\DashboardApiController::class, 'charts']);
    Route::get('dashboard/sdm-ringkasan', [Api\DashboardApiController::class, 'sdmRingkasan']);
    Route::get('dashboard/event-terkini', [Api\DashboardApiController::class, 'eventTerkini']);
    Route::get('dashboard/atlet-riwayat/{orang_id}', [Api\DashboardApiController::class, 'atletRiwayat']);

    Route::get('ekstrakurikuler', [Api\EkstrakurikulerController::class, 'index']);
    Route::get('ekstrakurikuler/{ekstrakurikuler}', [Api\EkstrakurikulerController::class, 'show']);
    Route::middleware('readonly')->group(function () {
        Route::post('ekstrakurikuler', [Api\EkstrakurikulerController::class, 'store']);
        Route::put('ekstrakurikuler/{ekstrakurikuler}', [Api\EkstrakurikulerController::class, 'update']);
        Route::delete('ekstrakurikuler/{ekstrakurikuler}', [Api\EkstrakurikulerController::class, 'destroy']);
    });

    // ══════════════════════════════════════════════════════════
    //  MASTER DATA — Hanya SuperAdmin & Admin Dispora Provinsi
    //  GET tetap terbuka (untuk dropdown/filter)
    // ══════════════════════════════════════════════════════════


    Route::get('cabor', [Api\CaborController::class, 'index']);
    Route::get('cabor/{cabor}', [Api\CaborController::class, 'show']);
    Route::get('kab-kota', [Api\KabKotaController::class, 'index']);
    Route::get('kab-kota/{kab_kotum}', [Api\KabKotaController::class, 'show']);
    Route::get('jenis', [Api\JenisController::class, 'index']);
    Route::get('jenis/{jeni}', [Api\JenisController::class, 'show']);
    Route::get('peran', [Api\PeranController::class, 'index']);
    Route::get('peran/{peran}', [Api\PeranController::class, 'show']);
    Route::get('skala', [Api\SkalaController::class, 'index']);
    Route::get('skala/{skala}', [Api\SkalaController::class, 'show']);

    Route::middleware('role:SuperAdmin|Admin Dispora Provinsi')->group(function () {
        Route::post('cabor', [Api\CaborController::class, 'store']);
        Route::put('cabor/{cabor}', [Api\CaborController::class, 'update']);
        Route::delete('cabor/{cabor}', [Api\CaborController::class, 'destroy']);

        Route::post('kab-kota', [Api\KabKotaController::class, 'store']);
        Route::put('kab-kota/{kab_kotum}', [Api\KabKotaController::class, 'update']);
        Route::delete('kab-kota/{kab_kotum}', [Api\KabKotaController::class, 'destroy']);

        Route::post('jenis', [Api\JenisController::class, 'store']);
        Route::put('jenis/{jeni}', [Api\JenisController::class, 'update']);
        Route::delete('jenis/{jeni}', [Api\JenisController::class, 'destroy']);

        Route::post('peran', [Api\PeranController::class, 'store']);
        Route::put('peran/{peran}', [Api\PeranController::class, 'update']);
        Route::delete('peran/{peran}', [Api\PeranController::class, 'destroy']);

        Route::post('skala', [Api\SkalaController::class, 'store']);
        Route::put('skala/{skala}', [Api\SkalaController::class, 'update']);
        Route::delete('skala/{skala}', [Api\SkalaController::class, 'destroy']);
    });

    // ══════════════════════════════════════════════════════════
    //  KONTEN — Informasi & Pengumuman
    // ══════════════════════════════════════════════════════════

    Route::get('informasi', [Api\InformasiController::class, 'index']);
    Route::get('informasi/{informasi}', [Api\InformasiController::class, 'show']);
    Route::middleware('role:SuperAdmin|Admin Dispora Provinsi|Admin Bidang Provinsi')->group(function () {
        Route::post('informasi', [Api\InformasiController::class, 'store']);
        Route::put('informasi/{informasi}', [Api\InformasiController::class, 'update']);
        Route::delete('informasi/{informasi}', [Api\InformasiController::class, 'destroy']);
    });

    Route::get('pengumuman', [Api\PengumumanController::class, 'index']);
    Route::get('pengumuman/{pengumuman}', [Api\PengumumanController::class, 'show']);
    Route::middleware('role:SuperAdmin|Admin Dispora Provinsi')->group(function () {
        Route::post('pengumuman', [Api\PengumumanController::class, 'store']);
        Route::put('pengumuman/{pengumuman}', [Api\PengumumanController::class, 'update']);
        Route::delete('pengumuman/{pengumuman}', [Api\PengumumanController::class, 'destroy']);
    });

    // ══════════════════════════════════════════════════════════
    //  SISTEM — Hanya SuperAdmin
    // ══════════════════════════════════════════════════════════

    Route::middleware('role:SuperAdmin')->group(function () {
        Route::apiResource('users', Api\UserController::class);
        Route::get('log-sistem', [Api\LogSistemController::class, 'index']);
        Route::post('{model}/{id}/restore', [Api\RestoreController::class, 'restore']);
    });

    // ══════════════════════════════════════════════════════════
    //  OPERATOR SPORTIF — SuperAdmin & Admin Dispora Provinsi
    // ══════════════════════════════════════════════════════════
    Route::middleware('role:SuperAdmin|Admin Dispora Provinsi')->group(function () {
        Route::get('operators/{operator}', [Api\OperatorController::class, 'show'])->middleware('log-sensitive');
        Route::apiResource('operators', Api\OperatorController::class)->except('show');
    });

    // ── Import / Export (readonly protected) ─────────────────
    Route::get('template/{type}', [Api\ImportController::class, 'downloadTemplate']);
    Route::get('export/{type}', [Api\ExportController::class, 'export'])->middleware('log-sensitive');
    Route::post('import/{type}', [Api\ImportController::class, 'import'])->middleware('readonly');
});

// ── Public API (tanpa auth) — ISO 27001: Rate-limited ────────
Route::prefix('v1/public')->middleware(['throttle:60,1'])->group(function () {
    Route::get('pengumuman', [Api\PengumumanController::class, 'publicIndex']);

    // Orang — listing + summary + detail
    Route::get('orang',           [Api\OrangController::class, 'publicIndex']);
    Route::get('orang/summary',   [Api\OrangController::class, 'publicSummary']);
    Route::get('orang/{id}',      [Api\OrangController::class, 'publicShow']);

    // Prasarana
    Route::get('prasarana',       [Api\PrasaranaController::class, 'publicIndex']);

    // Master data (read-only)
    Route::get('cabor',           [Api\CaborController::class, 'index']);
    Route::get('kab-kota',        [Api\KabKotaController::class, 'index']);
    Route::get('jenis',           [Api\JenisController::class, 'index']);
    Route::get('peran',           [Api\PeranController::class, 'index']);

    // Event publik (kalender + detail)
    Route::get('events',                     [Api\EventController::class, 'publicIndex']);
    Route::get('events/{event}/riwayat',     [Api\EventController::class, 'riwayat']);

    // Organisasi publik
    Route::get('organisasi',                 [Api\OrganisasiController::class, 'publicIndex']);
    Route::get('organisasi/{id}',            [Api\OrganisasiController::class, 'publicShow']);
});
