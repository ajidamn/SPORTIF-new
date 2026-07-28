@extends('layouts.public-dark')

@push('styles')
<style>
/* ── INFORMASI INDEX STYLES ────────────────────────── */
.info-section {
    padding: 180px 0 100px; /* Increased to prevent floating navbar overlap */
    min-height: 100vh;
}

.info-card {
    background: rgba(255,255,255,0.02);
    border-radius: 16px;
    border: 1px solid var(--glass-border);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    overflow: hidden;
    position: relative;
}
.info-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 100%;
    background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.4s;
}
.info-card:hover {
    border-color: var(--tech-blue);
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(0,212,255,0.2);
}
.info-card:hover::before { opacity: 1; }

.info-img-wrapper {
    height: 200px;
    background: rgba(0,0,0,0.3);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}
.info-img-wrapper i {
    font-size: 4rem;
    color: rgba(255,255,255,0.1);
    transition: transform 0.5s ease;
}
.info-card:hover .info-img-wrapper i {
    transform: scale(1.1);
    color: var(--tech-blue-dim);
}

.info-body {
    padding: 24px;
}
.info-date {
    font-family: var(--font-tech);
    font-size: 0.8rem;
    color: var(--tech-blue);
    letter-spacing: 1px;
    margin-bottom: 12px;
}
.info-title {
    font-family: var(--font-display);
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 12px;
    line-height: 1.4;
    transition: color 0.3s;
}
.info-card:hover .info-title {
    color: var(--emerald);
}
.info-desc {
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.6;
    margin-bottom: 0;
}

/* Pagination Overrides */
.pagination {
    margin-bottom: 0;
}
.page-link {
    background: rgba(255,255,255,0.02) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    color: var(--text-primary) !important;
    border-radius: 8px !important;
    margin: 0 4px;
}
.page-link:hover, .page-item.active .page-link {
    background: var(--tech-blue) !important;
    color: var(--dark-bg) !important;
    border-color: var(--tech-blue) !important;
}
</style>
@endpush

@section('content')
<section class="info-section">
    <div class="section-container">
        
        <!-- HEADER -->
        <div class="row align-items-center mb-5 reveal">
            <div class="col-md-8">
                <div class="section-label">Pusat Berita</div>
                <h1 class="section-heading mb-0" style="font-size:2.5rem;">Informasi & <span style="color:var(--tech-blue)">Berita</span></h1>
                <p class="section-desc mt-2 mb-0">Publikasi terkini kegiatan olahraga, kepemudaan dan kepramukaan di Jawa Timur.</p>
            </div>
            <div class="col-md-4 mt-4 mt-md-0 text-md-end">
                <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4" style="background:rgba(0,212,255,0.1); border:1px solid rgba(0,212,255,0.2);">
                    <div style="width:48px;height:48px;border-radius:12px;background:var(--tech-blue);display:flex;align-items:center;justify-content:center;color:var(--dark-bg);font-size:1.5rem;">
                        <i class="bi bi-newspaper"></i>
                    </div>
                    <div class="text-start">
                        <div class="font-tech fw-bold" style="font-size:1.5rem; color:var(--tech-blue); line-height:1;">Publikasi</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px;">Berita Resmi Dispora</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 reveal reveal-delay-1">
            @forelse($informasi as $item)
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('informasi.show', $item->slug) }}" class="text-decoration-none">
                    <div class="info-card">
                        <div class="info-img-wrapper">
                            <i class="bi bi-image"></i>
                        </div>
                        <div class="info-body">
                            <div class="info-date"><i class="bi bi-calendar-event me-2"></i>{{ $item->created_at->format('d M Y') }}</div>
                            <h3 class="info-title">{{ Str::limit($item->judul, 70) }}</h3>
                            <p class="info-desc">{{ Str::limit(strip_tags($item->isi), 120) }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-journal-x opacity-25 d-block mb-3" style="font-size:4rem;"></i>
                    <h5 class="text-secondary font-display fw-bold">Belum Ada Informasi</h5>
                    <p class="text-muted small">Saat ini belum ada artikel atau berita yang dipublikasikan.</p>
                </div>
            </div>
            @endforelse
        </div>

        <div class="mt-5 d-flex justify-content-center reveal reveal-delay-2">
            {{ $informasi->links() }}
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
