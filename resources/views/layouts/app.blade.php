<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SPORTIF') — Dispora Jawa Timur</title>
    <meta name="description" content="@yield('description', 'Sistem Informasi Data Keolahragaan, Kepemudaan & Kepramukaan Provinsi Jawa Timur')">
    <link rel="icon" type="image/png" href="{{ asset('logo/4_sportif.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg fixed-top" id="mainNavbar">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing') }}">
                <img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" style="height:40px;width:auto;">
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="bi bi-list"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto align-items-center gap-lg-1">
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}" href="{{ route('landing') }}">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('orang.public') ? 'active' : '' }}" href="{{ route('orang.public') }}">Data SDM</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('prasarana.public') ? 'active' : '' }}" href="{{ route('prasarana.public') }}">Prasarana</a></li>
                    <li class="nav-item"><a class="nav-link {{ request()->routeIs('informasi.*') ? 'active' : '' }}" href="{{ route('informasi.index') }}">Informasi</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-nav-login" href="{{ route('admin.login') }}">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main>@yield('content')</main>

    {{-- Footer --}}
    <footer class="site-footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF" style="height:44px;width:auto;">
                    </div>
                    <p class="text-muted small mb-0" style="max-width:380px;">
                        Sistem Informasi Pengelolaan Data Keolahragaan, Kepemudaan & Kepramukaan
                        Provinsi Jawa Timur
                    </p>
                </div>
                <div class="col-lg-3">
                    <h6 class="footer-heading">Menu</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ route('landing') }}" class="footer-link">Beranda</a></li>
                        <li class="mb-2"><a href="{{ route('orang.public') }}" class="footer-link">Data SDM</a></li>
                        <li class="mb-2"><a href="{{ route('prasarana.public') }}" class="footer-link">Prasarana</a></li>
                        <li class="mb-2"><a href="{{ route('informasi.index') }}" class="footer-link">Informasi</a></li>
                    </ul>
                </div>
                <div class="col-lg-4">
                    <h6 class="footer-heading">Kontak</h6>
                    <ul class="list-unstyled small text-muted">
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Jl. Kayoon No.56, Surabaya</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i>(031) 5344927</li>
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i>dispora@jatimprov.go.id</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color:rgba(255,255,255,0.08);">
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <small class="text-muted">&copy; {{ date('Y') }} Dinas Kepemudaan dan Olahraga Provinsi Jawa Timur</small>
                <small class="text-muted">SPORTIF v1.0</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            document.getElementById('mainNavbar').classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
    @stack('scripts')
</body>
</html>
