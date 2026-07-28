@extends('layouts.app')
@section('title', 'Beranda')
@section('description', 'Sistem Informasi Data Keolahragaan, Kepemudaan & Kepramukaan Provinsi Jawa Timur')

@push('styles')
<style>
/* ── Event Calendar ─────────────────────────────────── */
.cal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.cal-nav-btn { background:none; border:1px solid #e2e8f0; border-radius:10px; width:36px; height:36px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:.2s; color:#334155; }
.cal-nav-btn:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
.cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.cal-day-name { text-align:center; font-size:.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; padding:6px 0; }
.cal-cell { text-align:center; padding:8px 4px; border-radius:10px; font-size:.85rem; cursor:default; transition:.15s; position:relative; min-height:40px; display:flex; align-items:center; justify-content:center; }
.cal-cell.today { background:rgba(26,86,219,.08); font-weight:700; color:var(--primary); }
.cal-cell.has-event { cursor:pointer; font-weight:600; }
.cal-cell.has-event::after { content:''; position:absolute; bottom:4px; left:50%; transform:translateX(-50%); width:6px; height:6px; border-radius:50%; background:var(--primary); }
.cal-cell.has-event:hover { background:var(--primary); color:#fff; }
.cal-cell.has-event:hover::after { background:#fff; }
.cal-cell.selected { background:var(--primary); color:#fff; box-shadow:0 4px 12px rgba(26,86,219,.3); }
.cal-cell.selected::after { background:#fff; }
.cal-cell.other-month { color:#cbd5e1; }
.event-list-item { padding:12px; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:8px; transition:.2s; background:#fff; }
.event-list-item:hover { border-color:var(--primary); box-shadow:0 2px 8px rgba(26,86,219,.1); }
.event-type-badge { font-size:.7rem; padding:3px 8px; border-radius:6px; font-weight:600; }
</style>
@endpush

@section('content')

{{-- ══ HERO ════════════════════════════════════════════════════ --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7 hero-content">
                <div class="badge bg-primary bg-opacity-15 text-primary rounded-pill px-3 py-2 mb-3 fw-semibold" style="font-size:.85rem">
                    <i class="bi bi-trophy-fill me-1"></i>DISPORA JAWA TIMUR
                </div>
                <h1>Sistem Informasi<br><span class="gradient-text">Keolahragaan</span><br>Jawa Timur</h1>
                <p class="lead" style="opacity:.85">Pengelolaan data Keolahragaan, Kepemudaan &amp; Kepramukaan yang terintegrasi untuk mendukung pembangunan prestasi olahraga Provinsi Jawa Timur.</p>
                <div class="d-flex gap-3 flex-wrap mt-4">
                    <a href="{{ route('orang.public') }}" class="hero-btn hero-btn-primary">
                        <i class="bi bi-search me-1"></i> Jelajahi Data SDM
                    </a>
                    <a href="{{ route('prasarana.public') }}" class="hero-btn hero-btn-outline">
                        <i class="bi bi-geo-alt me-1"></i> Prasarana
                    </a>
                    <a href="{{ route('informasi.index') }}" class="hero-btn hero-btn-outline">
                        <i class="bi bi-newspaper me-1"></i> Informasi
                    </a>
                </div>

                {{-- STAT COUNTERS --}}
                <div class="hero-stats mt-5">
                    <div>
                        <div class="hero-stat-value counter-anim" data-target="{{ $stats['total_orang'] }}">0</div>
                        <div class="hero-stat-label">Insan Olahraga</div>
                    </div>
                    <div>
                        <div class="hero-stat-value counter-anim" data-target="{{ $stats['total_cabor'] }}">0</div>
                        <div class="hero-stat-label">Cabang Olahraga</div>
                    </div>
                    <div>
                        <div class="hero-stat-value counter-anim" data-target="{{ $stats['total_kabkota'] }}">0</div>
                        <div class="hero-stat-label">Kab/Kota</div>
                    </div>
                    <div>
                        <div class="hero-stat-value counter-anim" data-target="{{ $stats['total_event'] }}">0</div>
                        <div class="hero-stat-label">Event Terdaftar</div>
                    </div>
                </div>
            </div>

            {{-- Right side decoration --}}
            <div class="col-lg-5 d-none d-lg-block">
                <div style="position:relative;padding:2rem">
                    <div style="background:linear-gradient(135deg,rgba(26,86,219,.12),rgba(139,92,246,.12));border-radius:24px;padding:2rem;backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3)">
                        <div class="row g-3">
                            @php
                            $featureCards = [
                                ['icon'=>'bi-lightning-charge-fill','title'=>'Olahraga Prestasi','color'=>'#f59e0b','bg'=>'rgba(245,158,11,.15)'],
                                ['icon'=>'bi-heart-pulse-fill','title'=>'Olahraga Masyarakat','color'=>'#10b981','bg'=>'rgba(16,185,129,.15)'],
                                ['icon'=>'bi-flag-fill','title'=>'Kepemudaan','color'=>'#1a56db','bg'=>'rgba(26,86,219,.15)'],
                                ['icon'=>'bi-compass-fill','title'=>'Kepramukaan','color'=>'#8b5cf6','bg'=>'rgba(139,92,246,.15)'],
                            ];
                            @endphp
                            @foreach($featureCards as $fc)
                            <div class="col-6">
                                <div style="background:{{ $fc['bg'] }};border-radius:16px;padding:1.2rem;text-align:center;border:1px solid rgba(255,255,255,.2)">
                                    <div style="font-size:2rem;color:{{ $fc['color'] }}" class="mb-2"><i class="bi {{ $fc['icon'] }}"></i></div>
                                    <div class="fw-semibold" style="font-size:.82rem;color:#fff">{{ $fc['title'] }}</div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ FITUR ════════════════════════════════════════════════════ --}}
<section class="section">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Layanan <span style="color:var(--primary)">SPORTIF</span></h2>
            <p class="section-sub">Platform pengelolaan data olahraga terintegrasi Provinsi Jawa Timur</p>
        </div>
        <div class="row g-4">
            @php
            $features = [
                ['icon'=>'bi-lightning-fill','color'=>'#f59e0b','bg'=>'#fef3c7','title'=>'Olahraga Prestasi','desc'=>'Pendataan atlet, pelatih, wasit/juri beserta cabang olahraga dan prestasi yang dicapai.','link'=>route('orang.public')],
                ['icon'=>'bi-people-fill','color'=>'#1a56db','bg'=>'#dbeafe','title'=>'Olahraga Masyarakat','desc'=>'Pengelolaan data olahraga rekreasi dan masyarakat untuk mendukung budaya hidup sehat.','link'=>route('orang.public')],
                ['icon'=>'bi-flag-fill','color'=>'#10b981','bg'=>'#d1fae5','title'=>'Kepemudaan','desc'=>'Pendataan organisasi kemasyarakatan pemuda (OKP) dan kegiatan kepemudaan.','link'=>route('orang.public')],
                ['icon'=>'bi-compass-fill','color'=>'#8b5cf6','bg'=>'#ede9fe','title'=>'Kepramukaan','desc'=>'Pengelolaan data pembina, anggota pramuka, kwarda & kwarcab se-Jawa Timur.','link'=>route('orang.public')],
                ['icon'=>'bi-geo-alt-fill','color'=>'#ef4444','bg'=>'#fee2e2','title'=>'Data Prasarana','desc'=>'Pemetaan dan pendataan prasarana olahraga dengan integrasi peta interaktif.','link'=>route('prasarana.public')],
                ['icon'=>'bi-calendar-event-fill','color'=>'#ec4899','bg'=>'#fce7f3','title'=>'Event & Perlombaan','desc'=>'Pendataan event, perlombaan, dan pencapaian prestasi atlet Jawa Timur.','link'=>route('orang.public')],
            ];
            @endphp
            @foreach($features as $f)
            <div class="col-lg-4 col-md-6">
                <a href="{{ $f['link'] }}" class="text-decoration-none">
                    <div class="feature-card" style="transition:.25s" onmouseenter="this.style.transform='translateY(-5px)'" onmouseleave="this.style.transform=''">
                        <div class="f-icon" style="background:{{ $f['bg'] }};color:{{ $f['color'] }}">
                            <i class="bi {{ $f['icon'] }}"></i>
                        </div>
                        <h5>{{ $f['title'] }}</h5>
                        <p>{{ $f['desc'] }}</p>
                        <span style="color:{{ $f['color'] }};font-size:.85rem;font-weight:600">Lihat Data <i class="bi bi-arrow-right ms-1"></i></span>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ KALENDER EVENT ═══════════════════════════════════════════ --}}
<section class="section" style="background:#fff">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Kalender <span style="color:var(--primary)">Event</span></h2>
            <p class="section-sub">Jadwal kegiatan dan event olahraga Provinsi Jawa Timur</p>
        </div>
        <div class="row g-4">
            {{-- Kolom 8: Kalender --}}
            <div class="col-lg-8">
                <div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.05)">
                    <div class="cal-header">
                        <button class="cal-nav-btn" onclick="changeMonth(-1)"><i class="bi bi-chevron-left"></i></button>
                        <h5 class="fw-bold mb-0" id="calMonthTitle">—</h5>
                        <button class="cal-nav-btn" onclick="changeMonth(1)"><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <div class="cal-grid">
                        <div class="cal-day-name">Min</div>
                        <div class="cal-day-name">Sen</div>
                        <div class="cal-day-name">Sel</div>
                        <div class="cal-day-name">Rab</div>
                        <div class="cal-day-name">Kam</div>
                        <div class="cal-day-name">Jum</div>
                        <div class="cal-day-name">Sab</div>
                    </div>
                    <div class="cal-grid" id="calendarBody"></div>
                </div>
            </div>

            {{-- Kolom 4: Info event --}}
            <div class="col-lg-4">
                <div style="background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,.05);min-height:400px">
                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-calendar-event me-2 text-primary"></i>
                        <span id="eventPanelTitle">Event Bulan Ini</span>
                    </h6>
                    <div id="eventListPanel">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-calendar3 d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                            <span class="small">Klik tanggal untuk melihat event</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ INFORMASI TERBARU ═══════════════════════════════════════ --}}
@if($informasi->count() > 0)
<section class="section" style="background:#f8fafc">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="section-title mb-1">Informasi Terbaru</h2>
                <p class="section-sub mb-0">Berita dan kegiatan terkini</p>
            </div>
            <a href="{{ route('informasi.index') }}" class="btn btn-outline-primary rounded-pill px-4">
                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="row g-4">
            @foreach($informasi as $item)
            <div class="col-lg-4 col-md-6">
                <a href="{{ route('informasi.show', $item->slug) }}" class="text-decoration-none">
                    <div class="info-card" style="transition:.25s" onmouseenter="this.style.transform='translateY(-4px)'" onmouseleave="this.style.transform=''">
                        <div class="card-img d-flex align-items-center justify-content-center" style="background:linear-gradient(135deg,#e2e8f0,#f1f5f9)">
                            <i class="bi bi-newspaper" style="font-size:2.5rem;color:#94a3b8"></i>
                        </div>
                        <div class="card-body">
                            <div class="card-date mb-2">
                                <i class="bi bi-calendar3 me-1"></i>{{ $item->created_at->format('d M Y') }}
                            </div>
                            <h5>{{ Str::limit($item->judul, 60) }}</h5>
                            <p class="text-muted small">{{ Str::limit(strip_tags($item->isi), 100) }}</p>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══ CTA ══════════════════════════════════════════════════════ --}}
<section class="section" style="background:linear-gradient(135deg,#1e3a8a,#1a56db,#7c3aed)">
    <div class="container text-center text-white">
        <h2 class="fw-bold mb-3" style="font-size:2rem">Siap Mengelola Data Olahraga?</h2>
        <p class="mb-4 opacity-75">Masuk ke Panel Admin untuk menambah dan memperbarui data insan olahraga, prasarana, dan event.</p>
        <a href="{{ route('admin.login') }}" class="btn btn-light btn-lg rounded-pill px-5 fw-semibold" style="color:#1a56db">
            <i class="bi bi-shield-lock-fill me-2"></i>Masuk Admin
        </a>
    </div>
</section>

{{-- Pengumuman Modal --}}
@if($pengumuman->count() > 0)
<div class="modal fade" id="pengumumanModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius:20px;overflow:hidden">
            <div class="modal-header bg-gradient-primary text-white" style="padding:20px 24px">
                <h5 class="modal-title fw-bold"><i class="bi bi-megaphone-fill me-2"></i>Pengumuman</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                @foreach($pengumuman as $p)
                <div class="{{ !$loop->last ? 'border-bottom' : '' }}">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="pengumuman-icon {{ $p->is_pinned ? 'pinned' : '' }}">
                            <i class="bi {{ $p->is_pinned ? 'bi-pin-fill' : 'bi-bell-fill' }}"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">{{ $p->judul }}</h6>
                            <p class="text-muted mb-1 small">{!! nl2br(e(Str::limit($p->isi, 250))) !!}</p>
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

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Pengumuman popup
    @if($pengumuman->count() > 0)
    const key = 'pub_pengumuman_{{ now()->format("Ymd") }}';
    if (!sessionStorage.getItem(key)) {
        new bootstrap.Modal(document.getElementById('pengumumanModal')).show();
        sessionStorage.setItem(key, '1');
    }
    @endif

    // Counter animation
    document.querySelectorAll('.counter-anim').forEach(el => {
        const target = parseInt(el.dataset.target);
        if (!target) return;
        let current = 0;
        const step = Math.max(1, Math.ceil(target / 80));
        const timer = setInterval(() => {
            current = Math.min(current + step, target);
            el.textContent = current.toLocaleString('id-ID');
            if (current >= target) clearInterval(timer);
        }, 16);
    });

    // Init kalender
    initCalendar();
});

// ══════════════════════════════════════════════════════
//  KALENDER EVENT
// ══════════════════════════════════════════════════════
let calYear, calMonth, calEvents = [];
const BULAN = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

function initCalendar() {
    const now = new Date();
    calYear  = now.getFullYear();
    calMonth = now.getMonth(); // 0-indexed
    loadCalendarEvents();
}

function changeMonth(delta) {
    calMonth += delta;
    if (calMonth < 0) { calMonth = 11; calYear--; }
    if (calMonth > 11) { calMonth = 0; calYear++; }
    loadCalendarEvents();
}

async function loadCalendarEvents() {
    document.getElementById('calMonthTitle').textContent = `${BULAN[calMonth]} ${calYear}`;
    document.getElementById('eventPanelTitle').textContent = `Event ${BULAN[calMonth]} ${calYear}`;

    try {
        const r = await fetch(`/api/v1/public/events?month=${calMonth+1}&year=${calYear}&per_page=100`);
        const d = await r.json();
        calEvents = d.data || [];
    } catch(e) {
        calEvents = [];
    }

    renderCalendar();
    renderEventList(null); // tampilkan semua event bulan ini
}

function renderCalendar() {
    const body = document.getElementById('calendarBody');
    const today = new Date();
    const firstDay = new Date(calYear, calMonth, 1).getDay(); // 0=Sun
    const daysInMonth = new Date(calYear, calMonth+1, 0).getDate();
    const prevDays = new Date(calYear, calMonth, 0).getDate();

    // Tanggal yang ada event
    const eventDates = new Set();
    calEvents.forEach(ev => {
        const start = ev.tanggal_mulai ? new Date(ev.tanggal_mulai) : null;
        const end = ev.tanggal_selesai ? new Date(ev.tanggal_selesai) : start;
        if (start) {
            let cur = new Date(start);
            const endDate = end || start;
            while (cur <= endDate) {
                if (cur.getMonth() === calMonth && cur.getFullYear() === calYear) {
                    eventDates.add(cur.getDate());
                }
                cur.setDate(cur.getDate() + 1);
            }
        }
    });

    let html = '';
    // Hari kosong sebelum tanggal 1
    for (let i = firstDay - 1; i >= 0; i--) {
        html += `<div class="cal-cell other-month">${prevDays - i}</div>`;
    }
    // Tanggal bulan ini
    for (let d = 1; d <= daysInMonth; d++) {
        const isToday = (d === today.getDate() && calMonth === today.getMonth() && calYear === today.getFullYear());
        const hasEvent = eventDates.has(d);
        let cls = 'cal-cell';
        if (isToday) cls += ' today';
        if (hasEvent) cls += ' has-event';
        html += `<div class="${cls}" ${hasEvent ? `onclick="selectDate(this,${d})"` : ''}>${d}</div>`;
    }
    // Hari kosong setelah akhir bulan
    const totalCells = firstDay + daysInMonth;
    const remaining = (7 - (totalCells % 7)) % 7;
    for (let i = 1; i <= remaining; i++) {
        html += `<div class="cal-cell other-month">${i}</div>`;
    }
    body.innerHTML = html;
}

function selectDate(el, day) {
    document.querySelectorAll('.cal-cell.selected').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    renderEventList(day);
}

function renderEventList(day) {
    const panel = document.getElementById('eventListPanel');
    let events = calEvents;

    if (day) {
        const selDate = `${calYear}-${String(calMonth+1).padStart(2,'0')}-${String(day).padStart(2,'0')}`;
        events = calEvents.filter(ev => {
            const start = ev.tanggal_mulai || '';
            const end = ev.tanggal_selesai || start;
            return selDate >= start.substring(0,10) && selDate <= end.substring(0,10);
        });
        document.getElementById('eventPanelTitle').textContent = `Event ${day} ${BULAN[calMonth]} ${calYear}`;
    } else {
        document.getElementById('eventPanelTitle').textContent = `Event ${BULAN[calMonth]} ${calYear}`;
    }

    if (!events.length) {
        panel.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;opacity:.4"></i>
                <span class="small">${day ? 'Tidak ada event pada tanggal ini' : 'Tidak ada event bulan ini'}</span>
            </div>`;
        return;
    }

    const statusColors = { aktif:'success', selesai:'secondary', dibatalkan:'danger' };
    const typeColors = { 'single event':'primary', 'multi event':'info', 'pelatihan':'warning', 'perlombaan':'danger' };

    panel.innerHTML = events.map(ev => {
        const cabors = (ev.cabors||[]).map(c => c.nama).join(', ');
        const sColor = statusColors[ev.status] || 'secondary';
        const tColor = typeColors[ev.jenis_event] || 'secondary';
        const tgl = ev.tanggal_mulai
            ? new Date(ev.tanggal_mulai).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})
            : '—';
        const tglEnd = ev.tanggal_selesai
            ? new Date(ev.tanggal_selesai).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'})
            : '';

        return `
        <div class="event-list-item">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h6 class="fw-bold mb-0" style="font-size:.88rem">${ev.nama}</h6>
                <span class="badge bg-${sColor} bg-opacity-15 text-${sColor}" style="font-size:.68rem">${ev.status||'—'}</span>
            </div>
            <div class="d-flex flex-wrap gap-1 mb-2">
                <span class="event-type-badge bg-${tColor} bg-opacity-10 text-${tColor}">${ev.jenis_event||'—'}</span>
                ${ev.jenis ? `<span class="event-type-badge bg-dark bg-opacity-10 text-dark">${ev.jenis.nama}</span>` : ''}
            </div>
            <div class="small text-muted">
                <i class="bi bi-calendar3 me-1"></i>${tgl}${tglEnd ? ' — '+tglEnd : ''}
            </div>
            ${ev.lokasi_kegiatan ? `<div class="small text-muted mt-1"><i class="bi bi-geo-alt me-1"></i>${ev.lokasi_kegiatan}</div>` : ''}
            ${cabors ? `<div class="small mt-1"><i class="bi bi-trophy me-1 text-warning"></i>${cabors}</div>` : ''}
        </div>`;
    }).join('');
}
</script>
@endpush
