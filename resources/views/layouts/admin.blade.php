<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Admin SPORTIF JATIM</title>
    <link rel="icon" type="image/png" href="{{ asset('logo/4_sportif.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    {{-- ═══ LOADING OVERLAY ═══ --}}
    <div id="pageLoader" class="page-loader">
        <div class="loader-content">
            <div class="loader-logo-wrap">
                <div class="loader-ring"></div>
                <img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" class="loader-logo">
            </div>
            <div class="loader-text">Memuat Data...</div>
            <div class="loader-bar"><div class="loader-bar-fill"></div></div>
        </div>
    </div>
    <style>
    .page-loader {
        position: fixed; inset: 0; z-index: 99999;
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #1a56db 100%);
        display: flex; align-items: center; justify-content: center;
        transition: opacity 0.5s ease, visibility 0.5s ease;
    }
    .page-loader.fade-out { opacity: 0; visibility: hidden; }
    .loader-content { text-align: center; }
    .loader-logo-wrap {
        position: relative; width: 120px; height: 120px;
        margin: 0 auto 24px; display: flex; align-items: center; justify-content: center;
    }
    .loader-ring {
        position: absolute; inset: 0;
        border: 3px solid rgba(255,255,255,0.08);
        border-top: 3px solid #f59e0b;
        border-right: 3px solid #ef4444;
        border-radius: 50%;
        animation: loaderSpin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
    }
    .loader-ring::before {
        content: ''; position: absolute; inset: 6px;
        border: 2px solid rgba(255,255,255,0.04);
        border-bottom: 2px solid #1a56db;
        border-left: 2px solid #3b82f6;
        border-radius: 50%;
        animation: loaderSpin 0.8s cubic-bezier(0.5, 0, 0.5, 1) infinite reverse;
    }
    @keyframes loaderSpin { 0% { transform: rotate(0); } 100% { transform: rotate(360deg); } }
    .loader-logo {
        width: 64px; height: 64px; object-fit: contain;
        animation: loaderPulse 2s ease-in-out infinite;
        filter: drop-shadow(0 0 20px rgba(245,158,11,0.3));
    }
    @keyframes loaderPulse {
        0%, 100% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.08); opacity: 0.85; }
    }
    .loader-text {
        color: rgba(255,255,255,0.8); font-family: 'Inter', sans-serif;
        font-size: 0.85rem; font-weight: 500; letter-spacing: 2px;
        text-transform: uppercase; margin-bottom: 16px;
        animation: loaderTextPulse 2s ease-in-out infinite;
    }
    @keyframes loaderTextPulse {
        0%, 100% { opacity: 0.8; } 50% { opacity: 0.4; }
    }
    .loader-bar {
        width: 200px; height: 3px; margin: 0 auto;
        background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden;
    }
    .loader-bar-fill {
        height: 100%; width: 0;
        background: linear-gradient(90deg, #f59e0b, #ef4444, #1a56db);
        border-radius: 3px;
        animation: loaderProgress 1.8s ease-in-out infinite;
    }
    @keyframes loaderProgress {
        0% { width: 0; margin-left: 0; }
        50% { width: 70%; margin-left: 15%; }
        100% { width: 0; margin-left: 100%; }
    }
    </style>
    {{-- Sidebar --}}
    <aside class="admin-sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" style="height:38px;width:auto;">
            <div><div class="brand-title">SPORTIF</div><div class="brand-sub">Admin Panel</div></div>
        </div>

        <nav class="sidebar-nav">
            @php
                $user = auth()->user();
                $isSuperAdmin  = $user->hasRole('SuperAdmin');
                $isAdminProv   = $user->hasRole('Admin Dispora Provinsi');
                $isAdminBidang = $user->hasRole('Admin Bidang Provinsi');

                $roles = $user->getRoleNames();
                $isEksekutif = $roles->contains(function ($role) {
                    return str_starts_with($role, 'Kepala') || str_starts_with($role, 'Ketua');
                });

                $canSeeDataOrang = true;
                $canSeePrasarana = !$isEksekutif || $user->hasAnyRole(['Kepala Dinas Provinsi', 'Kepala Dinas Kab/Kota', 'Ketua Koni Provinsi', 'Ketua Koni Kab/Kota', 'Ketua NPCI Provinsi', 'Ketua NPCI Kab/Kota']);
                $canSeeSarana = !$isEksekutif || $user->hasAnyRole(['Kepala Dinas Provinsi', 'Kepala Dinas Kab/Kota']);
                $canSeeEvent = !$isEksekutif || !$user->hasAnyRole(['Ketua Inorga Provinsi', 'Ketua Inorga Kab/Kota']);
                $canSeeOrganisasi = !$isEksekutif || $user->hasAnyRole(['Kepala Dinas Provinsi', 'Kepala Dinas Kab/Kota', 'Kepala Bidang Olahraga Prestasi', 'Kepala Bidang Olahraga Masyarakat', 'Kepala Bidang Kepemudaan', 'Kepala Bidang Kepramukaan', 'Ketua Inorga Provinsi', 'Ketua Inorga Kab/Kota']);
                
                $canSeeDataMaster = $isSuperAdmin || $isAdminProv;
                $canSeeKonten     = $isSuperAdmin || $isAdminProv || $isAdminBidang;
                $canSeeSistem     = $isSuperAdmin;

                // Ekstrakurikuler: domain jenis_id = 2 (Olahraga Masyarakat)
                $canSeeEkskul = $isSuperAdmin || $isAdminProv
                    || $user->hasRole('Kepala Dinas Provinsi')
                    || $user->hasRole('Kepala Dinas Kab/Kota')
                    || $user->hasRole('Admin Dispora Kab/Kota')
                    || $user->hasRole('Kepala Bidang Olahraga Masyarakat')
                    || $user->hasAnyRole(['Ketua Kormi Provinsi', 'Ketua Kormi Kab/Kota'])
                    || ($user->jenis_id == 2);
            @endphp

            <div class="nav-section">MENU UTAMA</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
            </a>

            <div class="nav-section">INSAN OLAHRAGA</div>
            @if($canSeeDataOrang)
            <a href="{{ route('admin.orang') }}" class="nav-item {{ request()->routeIs('admin.orang') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i><span>Data Orang</span>
            </a>
            @endif
            @if($canSeePrasarana)
            <a href="{{ route('admin.prasarana') }}" class="nav-item {{ request()->routeIs('admin.prasarana') ? 'active' : '' }}">
                <i class="bi bi-geo-alt-fill"></i><span>Data Prasarana</span>
            </a>
            @endif
            @if($canSeeSarana)
            <a href="{{ route('admin.sarana') }}" class="nav-item {{ request()->routeIs('admin.sarana') ? 'active' : '' }}">
                <i class="bi bi-box-seam-fill"></i><span>Manajemen Sarana</span>
            </a>
            @endif
            @if($canSeeEvent)
            <a href="{{ route('admin.events') }}" class="nav-item {{ request()->routeIs('admin.events') ? 'active' : '' }}">
                <i class="bi bi-card-list"></i><span>Manajemen Event</span>
            </a>
            <a href="{{ route('admin.events.calendar') }}" class="nav-item {{ request()->routeIs('admin.events.calendar') ? 'active' : '' }}">
                <i class="bi bi-calendar-event-fill"></i><span>Kalender Event</span>
            </a>
            @endif
            @if($canSeeOrganisasi)
            <a href="{{ route('admin.organisasi') }}" class="nav-item {{ request()->routeIs('admin.organisasi') ? 'active' : '' }}">
                <i class="bi bi-building"></i><span>Data Organisasi</span>
            </a>
            @endif

            @if($canSeeEkskul)
            <div class="nav-section">PENDATAAN SEKOLAH</div>
            <a href="{{ route('admin.sekolah') }}" class="nav-item {{ request()->routeIs('admin.sekolah') ? 'active' : '' }}">
                <i class="bi bi-mortarboard-fill"></i><span>Data Sekolah</span>
            </a>
            <a href="{{ route('admin.master.jenis-ekstrakurikuler') }}" class="nav-item {{ request()->routeIs('admin.master.jenis-ekstrakurikuler') ? 'active' : '' }}">
                <i class="bi bi-bookmarks-fill"></i><span>Jenis Ekskul</span>
            </a>
            @endif
            
            <a href="{{ route('admin.pengaturan') }}" class="nav-item {{ request()->routeIs('admin.pengaturan') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i><span>Pengaturan</span>
            </a>
            <a href="{{ route('admin.aduan.index') }}" class="nav-item {{ request()->routeIs('admin.aduan.*') ? 'active' : '' }}">
                <i class="bi bi-ticket-detailed-fill"></i><span>Aduan & Bantuan</span>
            </a>

            @if($canSeeKonten)
            <div class="nav-section">KONTEN</div>
            <a href="{{ route('admin.informasi') }}" class="nav-item {{ request()->routeIs('admin.informasi') ? 'active' : '' }}">
                <i class="bi bi-newspaper"></i><span>Informasi</span>
            </a>
            <a href="{{ route('admin.pengumuman') }}" class="nav-item {{ request()->routeIs('admin.pengumuman') ? 'active' : '' }}">
                <i class="bi bi-megaphone-fill"></i><span>Pengumuman</span>
            </a>
            @endif

            @if($canSeeDataMaster)
            <div class="nav-section">DATA MASTER</div>
            <a href="{{ route('admin.master.jenis') }}" class="nav-item {{ request()->routeIs('admin.master.jenis') ? 'active' : '' }}">
                <i class="bi bi-tag-fill"></i><span>Jenis</span>
            </a>
            <a href="{{ route('admin.master.peran') }}" class="nav-item {{ request()->routeIs('admin.master.peran') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i><span>Peran</span>
            </a>
            <a href="{{ route('admin.master.cabor') }}" class="nav-item {{ request()->routeIs('admin.master.cabor') ? 'active' : '' }}">
                <i class="bi bi-dribbble"></i><span>Cabor</span>
            </a>
            <a href="{{ route('admin.master.kab-kota') }}" class="nav-item {{ request()->routeIs('admin.master.kab-kota') ? 'active' : '' }}">
                <i class="bi bi-map-fill"></i><span>Kab/Kota</span>
            </a>
            <a href="{{ route('admin.master.skala') }}" class="nav-item {{ request()->routeIs('admin.master.skala') ? 'active' : '' }}">
                <i class="bi bi-layers-fill"></i><span>Skala</span>
            </a>
            @endif

            @if($canSeeSistem || $isAdminProv)
            <div class="nav-section">SISTEM</div>
            @if($isSuperAdmin || $isAdminProv)
            <a href="{{ route('admin.operators') }}" class="nav-item {{ request()->routeIs('admin.operators') ? 'active' : '' }}">
                <i class="bi bi-person-vcard-fill"></i><span>Data Operator</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('SuperAdmin'))
            <a href="{{ route('admin.users') }}" class="nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i><span>Users & Roles</span>
            </a>
            <a href="{{ route('admin.log-sistem') }}" class="nav-item {{ request()->routeIs('admin.log-sistem') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i><span>Log Sistem</span>
            </a>
            @endif
            @endif
        </nav>
    </aside>

    {{-- Main --}}
    <div class="admin-main">
        <header class="admin-header">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm border-0 d-lg-none" id="sidebarToggle"><i class="bi bi-list fs-4"></i></button>
                <h5 class="mb-0 fw-bold">@yield('title', 'Dashboard')</h5>
            </div>
            <div class="d-flex align-items-center gap-3">
                {{-- Notification Dropdown --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border-0 position-relative rounded-circle p-2 d-flex align-items-center justify-content-center" data-bs-toggle="dropdown" style="width: 38px; height: 38px;">
                        <i class="bi bi-bell-fill fs-5 text-secondary"></i>
                        <span id="notificationBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ auth()->user()->unreadNotifications->count() == 0 ? 'd-none' : '' }}" style="font-size:0.65rem;">
                            {{ auth()->user()->unreadNotifications->count() > 99 ? '99+' : auth()->user()->unreadNotifications->count() }}
                        </span>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-0 mt-2" style="width: 320px;">
                        <div class="p-3 border-bottom d-flex justify-content-between align-items-center bg-light rounded-top-4">
                            <h6 class="mb-0 fw-bold">Notifikasi</h6>
                            <form id="markAllReadForm" action="{{ route('admin.notifications.read-all') }}" method="POST" class="m-0 {{ auth()->user()->unreadNotifications->count() == 0 ? 'd-none' : '' }}">
                                @csrf
                                <button type="submit" class="btn btn-sm text-primary p-0 border-0 bg-transparent fw-medium" style="font-size: 0.8rem;">Tandai dibaca</button>
                            </form>
                        </div>
                        <div id="notificationList" class="overflow-auto flex-grow-1" style="max-height: 350px;">
                            @forelse(auth()->user()->unreadNotifications()->limit(10)->get() as $notification)
                            <div class="p-3 border-bottom bg-primary bg-opacity-10">
                                <form action="{{ route('admin.notifications.read', $notification->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn p-0 text-start border-0 bg-transparent w-100 d-flex gap-3">
                                        <div class="text-{{ $notification->data['color'] ?? 'primary' }} mt-1">
                                            <i class="bi {{ $notification->data['icon'] ?? 'bi-bell-fill' }} fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-1 small fw-bold text-dark">{{ $notification->data['title'] ?? 'Pemberitahuan' }}</h6>
                                            <p class="mb-1 small text-muted" style="line-height: 1.4;">{{ $notification->data['message'] ?? '' }}</p>
                                            <small class="text-muted" style="font-size: 0.7rem;">{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                    </button>
                                </form>
                            </div>
                            @empty
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2 opacity-50"></i>
                                <span class="small">Belum ada notifikasi terbaru.</span>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">
                    <i class="bi bi-person-badge me-1"></i>{{ auth()->user()->getRoleNames()->first() }}
                </span>
                <div class="dropdown">
                    <button class="btn btn-sm d-flex align-items-center gap-2 border-0" data-bs-toggle="dropdown">
                        <div class="avatar-sm">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        <span class="d-none d-md-inline small fw-medium">{{ auth()->user()->name }}</span>
                        <i class="bi bi-chevron-down small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><a class="dropdown-item small" href="{{ route('landing') }}" target="_blank"><i class="bi bi-globe me-2"></i>Lihat Website</a></li>
                        <li><a class="dropdown-item small" href="#" data-bs-toggle="modal" data-bs-target="#changePasswordModal"><i class="bi bi-shield-lock me-2"></i>Ubah Password</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button class="dropdown-item small text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <div class="admin-content">
            @yield('content')
        </div>
    </div>

    {{-- Pengumuman Modal --}}
    @if(isset($pengumuman) && $pengumuman->count() > 0)
    <div class="modal fade" id="pengumumanModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-gradient-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-megaphone-fill me-2"></i>Pengumuman</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    @foreach($pengumuman as $p)
                    <div class="pengumuman-item {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-start gap-3 p-3">
                            <div class="pengumuman-icon {{ $p->is_pinned ? 'pinned' : '' }}">
                                <i class="bi {{ $p->is_pinned ? 'bi-pin-fill' : 'bi-bell-fill' }}"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">{{ $p->judul }}</h6>
                                <p class="mb-1 text-muted small">{!! nl2br(e(Str::limit($p->isi, 200))) !!}</p>
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ $p->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Change Password Modal --}}
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-gradient-primary text-white border-0">
                    <h5 class="modal-title"><i class="bi bi-shield-lock-fill me-2"></i>Ubah Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="changePasswordForm">
                        <div id="passwordErrorAlert" class="alert alert-danger d-none small"></div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Password Saat Ini</label>
                            <input type="password" class="form-control" name="old_password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required>
                            <div class="form-text small mt-2">
                                <i class="bi bi-info-circle me-1"></i> Standar Keamanan (ISO 27001):
                                <ul class="mb-0 mt-1 ps-3">
                                    <li>Minimal 8 karakter</li>
                                    <li>Mengandung Huruf Besar & Kecil</li>
                                    <li>Mengandung Angka</li>
                                    <li>Mengandung Simbol Khusus (@$!%*#?&)</li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" name="new_password_confirmation" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary px-4" id="btnSavePassword">Simpan & Logout</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.11/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.11/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // CSRF for all AJAX
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });

        // Sidebar toggle
        document.getElementById('sidebarToggle')?.addEventListener('click', () => {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Toast helper
        const Toast = Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:3000, timerProgressBar:true });

        // Show pengumuman modal on page load
        @if(isset($pengumuman) && $pengumuman->count() > 0)
        document.addEventListener('DOMContentLoaded', () => {
            const shown = sessionStorage.getItem('pengumuman_shown_{{ now()->format('Y-m-d') }}');
            if (!shown) {
                new bootstrap.Modal(document.getElementById('pengumumanModal')).show();
                sessionStorage.setItem('pengumuman_shown_{{ now()->format('Y-m-d') }}', '1');
            }
        });
        @endif
    </script>
    <script>
    // Dismiss loader when page is fully interactive
    (function() {
        const loader = document.getElementById('pageLoader');
        if (!loader) return;
        function hideLoader() {
            loader.classList.add('fade-out');
            setTimeout(() => loader.remove(), 600);
        }
        // Hide after DOM content loaded + a short buffer
        if (document.readyState === 'complete') {
            hideLoader();
        } else {
            window.addEventListener('load', () => setTimeout(hideLoader, 300));
        }
        // Failsafe: force hide after 4 seconds max
        setTimeout(hideLoader, 4000);
    })();
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifBadge = document.getElementById('notificationBadge');
        const notifList = document.getElementById('notificationList');
        const markAllForm = document.getElementById('markAllReadForm');

        function fetchNotifications() {
            fetch('{{ route("admin.notifications.fetch") }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                // Update badge
                if (data.count > 0) {
                    notifBadge.classList.remove('d-none');
                    notifBadge.textContent = data.count > 99 ? '99+' : data.count;
                    if(markAllForm) markAllForm.classList.remove('d-none');
                } else {
                    notifBadge.classList.add('d-none');
                    if(markAllForm) markAllForm.classList.add('d-none');
                }

                // Render list
                if (data.notifications.length > 0) {
                    let html = '';
                    data.notifications.forEach(notif => {
                        let actionUrl = `{{ url('admin/notifications') }}/${notif.id}/read`;
                        let csrfToken = '{{ csrf_token() }}';
                        
                        html += `
                        <div class="p-3 border-bottom bg-primary bg-opacity-10">
                            <form action="${actionUrl}" method="POST" class="m-0">
                                <input type="hidden" name="_token" value="${csrfToken}">
                                <button type="submit" class="btn p-0 text-start border-0 bg-transparent w-100 d-flex gap-3">
                                    <div class="text-${notif.color} mt-1">
                                        <i class="bi ${notif.icon} fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 small fw-bold text-dark">${notif.title}</h6>
                                        <p class="mb-1 small text-muted" style="line-height: 1.4;">${notif.message}</p>
                                        <small class="text-muted" style="font-size: 0.7rem;">${notif.time}</small>
                                    </div>
                                </button>
                            </form>
                        </div>`;
                    });
                    notifList.innerHTML = html;
                } else {
                    notifList.innerHTML = `
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-bell-slash fs-3 d-block mb-2 opacity-50"></i>
                        <span class="small">Belum ada notifikasi terbaru.</span>
                    </div>`;
                }
            })
            .catch(err => console.error('Error fetching notifications:', err));
        }

        setInterval(fetchNotifications, 5000);
    });

    // Change Password AJAX
    document.getElementById('btnSavePassword')?.addEventListener('click', function() {
        const form = document.getElementById('changePasswordForm');
        const formData = new FormData(form);
        const errorAlert = document.getElementById('passwordErrorAlert');
        const btn = this;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
        errorAlert.classList.add('d-none');
        errorAlert.innerHTML = '';

        fetch('{{ route("admin.profile.password") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({status: response.status, body: data})))
        .then(result => {
            if (result.status === 422) {
                // Validation Error
                let errors = result.body.errors;
                let html = '<ul class="mb-0 ps-3">';
                for (let field in errors) {
                    errors[field].forEach(msg => {
                        html += `<li>${msg}</li>`;
                    });
                }
                html += '</ul>';
                errorAlert.innerHTML = html;
                errorAlert.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = 'Simpan & Logout';
            } else if (result.status === 200 && result.body.success) {
                window.location.href = result.body.redirect;
            } else {
                alert('Terjadi kesalahan yang tidak diketahui.');
                btn.disabled = false;
                btn.innerHTML = 'Simpan & Logout';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menghubungi server.');
            btn.disabled = false;
            btn.innerHTML = 'Simpan & Logout';
        });
    });
    </script>
    @stack('scripts')

    {{-- Modal Wajib Isi Email (non-dismissible) --}}
    @if(empty(auth()->user()->email))
    <div class="modal fade" id="emailRequiredModal" tabindex="-1" aria-labelledby="emailRequiredLabel" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="emailRequiredLabel">
                        <i class="bi bi-envelope-exclamation text-warning me-2"></i>Lengkapi Data Email
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3" style="font-size:0.9rem; line-height:1.6;">
                        Akun Anda belum memiliki alamat email. Silakan isi email Anda untuk keperluan keamanan dan komunikasi sistem.
                    </p>
                    <div id="emailModalAlert" class="alert alert-danger py-2 px-3 d-none" style="font-size:0.85rem;"></div>
                    <div class="mb-3">
                        <label class="form-label fw-medium" for="required_email">Alamat Email</label>
                        <input type="email" id="required_email" class="form-control" placeholder="contoh@email.com" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary w-100 fw-semibold" id="btnSaveRequiredEmail" style="border-radius:12px; padding:10px;">
                        <i class="bi bi-check-lg me-2"></i>Simpan Email
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        new bootstrap.Modal(document.getElementById('emailRequiredModal')).show();

        document.getElementById('btnSaveRequiredEmail').addEventListener('click', function() {
            const email = document.getElementById('required_email').value.trim();
            const alertEl = document.getElementById('emailModalAlert');
            const btn = this;

            alertEl.classList.add('d-none');

            if (!email || !email.includes('@')) {
                alertEl.textContent = 'Masukkan alamat email yang valid.';
                alertEl.classList.remove('d-none');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

            fetch('/api/v1/profile/email', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ email: email })
            })
            .then(r => r.json().then(data => ({status: r.status, body: data})))
            .then(result => {
                if (result.status === 200 && result.body.success) {
                    bootstrap.Modal.getInstance(document.getElementById('emailRequiredModal')).hide();
                    // Show success toast
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Email berhasil disimpan.', timer: 2000, showConfirmButton: false });
                    }
                } else if (result.status === 422) {
                    let errors = result.body.errors;
                    let msgs = [];
                    for (let f in errors) errors[f].forEach(m => msgs.push(m));
                    alertEl.textContent = msgs.join(', ');
                    alertEl.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Simpan Email';
                } else {
                    alertEl.textContent = result.body.message || 'Terjadi kesalahan.';
                    alertEl.classList.remove('d-none');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Simpan Email';
                }
            })
            .catch(() => {
                alertEl.textContent = 'Gagal menghubungi server.';
                alertEl.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg me-2"></i>Simpan Email';
            });
        });
    });
    </script>
    @endif
</body>
</html>
