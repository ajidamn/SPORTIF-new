@extends('layouts.public-dark')

@push('styles')
<style>
/* ── INFORMASI SHOW STYLES ────────────────────────── */
.article-header-section {
    padding: 160px 0 80px;
    position: relative;
    overflow: hidden;
}
.article-header-section::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at top right, rgba(0, 212, 255, 0.1) 0%, transparent 60%);
    z-index: -1;
}

.article-breadcrumb {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.05);
    border-radius: 50px;
    padding: 10px 24px;
    display: inline-flex;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
}
.breadcrumb {
    margin-bottom: 0;
}
.breadcrumb-item + .breadcrumb-item::before {
    color: rgba(255,255,255,0.3);
    content: "\F285"; /* bi-chevron-right */
    font-family: bootstrap-icons !important;
    font-size: 0.8em;
    vertical-align: middle;
}
.breadcrumb-item a {
    color: var(--tech-blue);
    text-decoration: none;
    font-family: var(--font-tech);
    font-size: 0.85rem;
    transition: color 0.3s;
}
.breadcrumb-item a:hover {
    color: var(--emerald);
}
.breadcrumb-item.active {
    color: var(--text-secondary);
    font-family: var(--font-tech);
    font-size: 0.85rem;
}

.article-title {
    font-family: var(--font-display);
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--text-primary);
    line-height: 1.3;
    margin-bottom: 24px;
}
@media (max-width: 768px) {
    .article-title { font-size: 2rem; }
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    color: var(--text-secondary);
    font-family: var(--font-tech);
    font-size: 0.9rem;
    padding-bottom: 30px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.meta-item {
    display: flex;
    align-items: center;
    gap: 8px;
}
.meta-item i {
    color: var(--tech-blue);
}

.article-content-card {
    background: var(--dark-card);
    border-radius: 24px;
    border: 1px solid var(--glass-border);
    padding: 40px;
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    margin-top: -40px;
    position: relative;
    z-index: 2;
}
@media (max-width: 768px) {
    .article-content-card { padding: 24px; }
}

.article-body {
    font-size: 1.05rem;
    line-height: 1.8;
    color: rgba(255,255,255,0.85);
}
.article-body h1, .article-body h2, .article-body h3, .article-body h4, .article-body h5, .article-body h6 {
    font-family: var(--font-display);
    color: var(--text-primary);
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 700;
}
.article-body p {
    margin-bottom: 1.5rem;
}
.article-body a {
    color: var(--tech-blue);
    text-decoration: none;
    border-bottom: 1px solid rgba(0, 212, 255, 0.3);
    transition: all 0.3s;
}
.article-body a:hover {
    color: var(--emerald);
    border-bottom-color: var(--emerald);
}
.article-body img {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    margin: 1.5rem 0;
}
.article-body blockquote {
    border-left: 4px solid var(--tech-blue);
    padding: 1rem 1.5rem;
    background: rgba(0, 212, 255, 0.05);
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: var(--text-secondary);
    margin: 1.5rem 0;
}
.article-body ul, .article-body ol {
    margin-bottom: 1.5rem;
    padding-left: 1.5rem;
}
.article-body li {
    margin-bottom: 0.5rem;
}

.attachment-box {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 20px;
    margin-top: 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}
.attachment-info {
    display: flex;
    align-items: center;
    gap: 12px;
}
.attachment-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(0, 255, 136, 0.1);
    color: var(--emerald);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
</style>
@endpush

@section('content')
<!-- HEADER SECTION -->
<section class="article-header-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 reveal">
                
                <div class="article-breadcrumb">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Beranda</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('informasi.index') }}">Informasi</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Membaca Artikel</li>
                        </ol>
                    </nav>
                </div>

                <h1 class="article-title">{{ $item->judul }}</h1>
                
                <div class="article-meta">
                    <div class="meta-item">
                        <i class="bi bi-calendar-event"></i>
                        {{ $item->created_at->format('d F Y') }}
                    </div>
                    <div class="meta-item">
                        <i class="bi bi-person-circle"></i>
                        {{ $item->author->name ?? 'Admin Dispora' }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

<!-- CONTENT SECTION -->
<section class="pb-5" style="min-height: 50vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                
                <div class="article-content-card reveal reveal-delay-1">
                    <div class="article-body">
                        {!! $item->isi !!}
                    </div>

                    @if($item->file_pendukung)
                    <div class="attachment-box">
                        <div class="attachment-info">
                            <div class="attachment-icon">
                                <i class="bi bi-file-earmark-arrow-down"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1 text-white">File Pendukung</h6>
                                <div class="small text-secondary font-tech">Lampiran terkait publikasi ini</div>
                            </div>
                        </div>
                        <a href="{{ asset('storage/' . $item->file_pendukung) }}" target="_blank" class="nav-cta border-0 rounded-pill px-4 py-2 text-decoration-none">
                            <i class="bi bi-download me-2"></i>Unduh File
                        </a>
                    </div>
                    @endif
                </div>

                <div class="mt-5 text-center reveal reveal-delay-2">
                    <a href="{{ route('informasi.index') }}" class="btn btn-outline-light rounded-pill px-4 py-2" style="border-color: rgba(255,255,255,0.2); font-family: var(--font-tech); font-size: 0.9rem;">
                        <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Informasi
                    </a>
                </div>

            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Reveal Animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 1s ease-out forwards';
                entry.target.style.opacity = '1';
                observer.unobserve(entry.target);
            }
        });
    });
    document.querySelectorAll('.reveal').forEach(el => {
        el.style.opacity = '0';
        observer.observe(el);
    });
});
</script>
@endpush
