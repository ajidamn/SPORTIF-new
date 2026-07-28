<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\LandingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — SPORTIF DISPORA JATIM
|--------------------------------------------------------------------------
*/

// ── Public Routes ──────────────────────────────────────────
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/informasi', [LandingController::class, 'informasiIndex'])->name('informasi.index');
Route::get('/informasi/{slug}', [LandingController::class, 'informasiShow'])->name('informasi.show');
Route::get('/data-orang', [LandingController::class, 'orangIndex'])->name('orang.public');
Route::get('/prasarana', [LandingController::class, 'prasaranaIndex'])->name('prasarana.public');
Route::get('/organisasi', [LandingController::class, 'organisasiIndex'])->name('organisasi.public');
Route::get('/landingpage2', [LandingController::class, 'landingpage2'])->name('landingpage2');

// ── Admin Auth ─────────────────────────────────────────────
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
Route::post('/admin/forgot-password', [AdminAuthController::class, 'submitForgotPassword'])->name('admin.forgot-password');

// ── Admin MFA ──────────────────────────────────────────────
Route::get('/admin/mfa/setup', [\App\Http\Controllers\Web\MfaController::class, 'setup'])->name('admin.mfa.setup')->middleware('auth');
Route::post('/admin/mfa/verify', [\App\Http\Controllers\Web\MfaController::class, 'verifySetup'])->name('admin.mfa.verify')->middleware('auth');

// ── Admin Panel (Authenticated) ────────────────────────────
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/events/{id}/peserta', [App\Http\Controllers\Web\EventPesertaController::class, 'index'])->name('events.peserta');
    
    // Organisasi Pengurus
    Route::get('/organisasi/{id}/pengurus', [App\Http\Controllers\Web\OrganisasiPengurusController::class, 'index'])->name('organisasi.pengurus');
    Route::get('/organisasi/{id}/pengurus/data', [App\Http\Controllers\Web\OrganisasiPengurusController::class, 'data']);
    Route::post('/organisasi/{id}/pengurus', [App\Http\Controllers\Web\OrganisasiPengurusController::class, 'store']);
    Route::delete('/organisasi/{id}/pengurus/{pengurus_id}', [App\Http\Controllers\Web\OrganisasiPengurusController::class, 'destroy']);

    // SPA-style admin pages — all use same layout, data loaded via API
    $pages = [
        'orang'              => 'Data Orang',
        'prasarana'          => 'Data Prasarana',
        'sarana'             => 'Manajemen Sarana',
        'events'             => 'Manajemen Event',
        'organisasi'         => 'Data Organisasi',
        'sekolah'            => 'Data Sekolah',
        'informasi'          => 'Manajemen Informasi',
        'pengumuman'         => 'Manajemen Pengumuman',
        'master/jenis'       => 'Data Master Jenis',
        'master/peran'       => 'Data Master Peran',
        'master/cabor'       => 'Data Master Cabor',
        'master/kab-kota'    => 'Data Master Kab/Kota',
        'master/skala'       => 'Data Master Skala',
        'master/jenis-ekstrakurikuler' => 'Data Master Jenis Ekstrakurikuler',
        'users'              => 'Manajemen User & Roles',
        'operators'          => 'Data Operator SPORTIF',
        'log-sistem'         => 'Log Sistem',
    ];


    foreach ($pages as $path => $title) {
        Route::get('/' . $path, function () use ($path, $title) {
            $user = auth()->user();
            $roles = $user->getRoleNames();
            $isReadOnly = $roles->contains(fn($r) => str_starts_with($r, 'Kepala') || str_starts_with($r, 'Ketua'));

            return view('admin.crud', [
                'pageTitle' => $title,
                'pageSlug'  => str_replace('/', '-', $path),
                'isReadOnly' => $isReadOnly,
            ]);
        })->name(str_replace('/', '.', $path));
    }

    // Profile & Password
    Route::post('/profile/password', [\App\Http\Controllers\Web\AdminAuthController::class, 'updatePassword'])->name('profile.password');

    // Pengaturan Profil
    Route::get('/pengaturan', function () {
        return view('admin.pengaturan', ['pageTitle' => 'Pengaturan Profil']);
    })->name('pengaturan');

    // Notifications
    Route::get('/notifications/fetch', [\App\Http\Controllers\Web\NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\Web\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [\App\Http\Controllers\Web\NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    // Custom Route for Sekolah Detail (Opsi B)
    Route::prefix('sekolah')->name('sekolah.')->group(function () {
        Route::get('/{sekolah}', [\App\Http\Controllers\Web\SekolahController::class, 'show'])->name('show');
    });

    // Ticketing (Aduan) Routes
    Route::prefix('aduan')->name('aduan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Web\TicketController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Web\TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [\App\Http\Controllers\Web\TicketController::class, 'show'])->name('show');
        Route::post('/{ticket}/reply', [\App\Http\Controllers\Web\TicketController::class, 'reply'])->name('reply');
        Route::post('/{ticket}/close', [\App\Http\Controllers\Web\TicketController::class, 'close'])->name('close');
        Route::get('/{ticket}/messages', [\App\Http\Controllers\Web\TicketController::class, 'fetchMessages'])->name('messages');
    });
});
