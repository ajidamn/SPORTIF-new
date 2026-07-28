@extends('layouts.public-dark')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<style>
/* ── PRASARANA STYLES ────────────────────────── */
.prasarana-section {
    padding: 180px 0 100px; /* Increased to prevent floating navbar overlap */
    min-height: 100vh;
}
.filter-card {
    background: var(--dark-card);
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    padding: 24px;
    margin-bottom: 30px;
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
}
.filter-card label {
    color: var(--text-secondary);
    font-size: 0.85rem;
    letter-spacing: 1px;
}
.filter-card select {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border-radius: 8px;
}
.filter-card select:focus {
    background: rgba(255, 255, 255, 0.06);
    border-color: var(--tech-blue);
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25);
    color: var(--text-primary);
}
.filter-card select option {
    background: var(--dark-bg);
    color: var(--text-primary);
}
.filter-card select:disabled {
    background: rgba(255,255,255,0.01);
    color: rgba(255,255,255,0.3);
}

#prasaranaMap {
    height: 500px;
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    background: var(--dark-surface);
    z-index: 1; /* Biar ngga nabrak navbar */
}
/* Leaflet Dark mode popup overrides */
.leaflet-popup-content-wrapper {
    background: rgba(10, 15, 30, 0.95);
    backdrop-filter: blur(10px);
    color: var(--text-primary);
    border: 1px solid var(--glass-border);
    border-radius: 12px;
}
.leaflet-popup-tip {
    background: rgba(10, 15, 30, 0.95);
    border: 1px solid var(--glass-border);
}

.prasarana-card {
    background: rgba(255,255,255,0.02);
    border-radius: 16px;
    border: 1px solid var(--glass-border);
    padding: 20px;
    margin-bottom: 24px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    height: 100%;
}
.prasarana-card:hover {
    border-color: var(--tech-blue);
    background: rgba(0,212,255,0.05);
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(0,212,255,0.2);
}
.foto-thumb {
    width: 100%;
    height: 160px;
    object-fit: cover;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.05);
}
.foto-placeholder {
    width: 100%;
    height: 160px;
    background: rgba(255,255,255,0.03);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255,255,255,0.2);
    font-size: 3rem;
    border: 1px solid rgba(255,255,255,0.05);
}

.btn-gmaps {
    background: linear-gradient(135deg, #10b981, #059669);
    color: var(--dark-bg);
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-weight: 700;
    font-size: .85rem;
    transition: .2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-gmaps:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(16,185,129,.4);
    color: var(--dark-bg);
}

/* Modal Dark overrides */
.modal-content {
    background: rgba(10, 15, 30, 0.98);
    backdrop-filter: blur(20px);
    border: 1px solid var(--glass-border);
    color: var(--text-primary);
}
.modal-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.modal-header .btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
.bg-light-dark {
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05);
}
.table-dark-glass {
    color: var(--text-primary);
    border-color: rgba(255,255,255,0.1);
}
.table-dark-glass thead th {
    background: rgba(255,255,255,0.05);
    border-bottom: 2px solid rgba(255,255,255,0.1);
    color: var(--tech-blue);
}
.table-dark-glass tbody td {
    background: transparent;
    border-color: rgba(255,255,255,0.05);
}
/* DataTables Processing */
div.dataTables_wrapper div.dataTables_processing {
    background: rgba(10, 15, 30, 0.95) !important;
    color: var(--text-primary) !important;
    border: 1px solid var(--tech-blue) !important;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    z-index: 10;
}
</style>
@endpush

@section('content')
<section class="prasarana-section">
    <div class="section-container">
        
        <!-- HEADER -->
        <div class="row align-items-center mb-5 reveal">
            <div class="col-md-8">
                <div class="section-label">Pemetaan Infrastruktur</div>
                <h1 class="section-heading mb-0" style="font-size:2.5rem;">Data <span style="color:var(--emerald)">Prasarana</span></h1>
                <p class="section-desc mt-2 mb-0">Peta dan daftar fasilitas olahraga serta kepemudaan se-Jawa Timur terintegrasi dengan pemetaan geografis.</p>
            </div>
            <div class="col-md-4 mt-4 mt-md-0 text-md-end">
                <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4" style="background:rgba(0,255,136,0.1); border:1px solid rgba(0,255,136,0.2);">
                    <div style="width:48px;height:48px;border-radius:12px;background:var(--emerald);display:flex;align-items:center;justify-content:center;color:var(--dark-bg);font-size:1.5rem;">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="text-start">
                        <div class="font-tech fw-bold" style="font-size:1.5rem; color:var(--emerald); line-height:1;" id="countPrasarana">—</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px;">Total Prasarana</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="filter-card reveal reveal-delay-1">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-tag me-1" style="color:var(--tech-blue)"></i> Domain
                    </label>
                    <select class="form-select form-select-sm" id="filterJenisP">
                        <option value="">Semua Domain</option>
                        @foreach($jenis as $j)
                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-tags me-1" style="color:var(--emerald)"></i> Kategori
                    </label>
                    <select class="form-select form-select-sm" id="filterKategoriP">
                        <option value="">Semua Kategori</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-dribbble me-1" style="color:var(--victory-gold)"></i> Cabor
                    </label>
                    <select class="form-select form-select-sm" id="filterCaborMap" disabled>
                        <option value="">Pilih Domain Dulu</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-award me-1" style="color:var(--neon-purple)"></i> Standar
                    </label>
                    <select class="form-select form-select-sm" id="filterStandarP">
                        <option value="">Semua Standar</option>
                        <option value="Belum di Standarisasi">Belum di Standarisasi</option>
                        <option value="Regional">Regional</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-map me-1" style="color:var(--tech-blue-dim)"></i> Kab/Kota
                    </label>
                    <select class="form-select form-select-sm" id="filterLokasiMap">
                        <option value="">Semua Kab/Kota</option>
                        @foreach($kabKota as $k)
                        <option value="{{ $k->id }}">{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-person-badge me-1" style="color:var(--emerald-dim)"></i> Pengelola
                    </label>
                    <select class="form-select form-select-sm" id="filterPengelola">
                        <option value="">Semua</option>
                        <option value="Pemerintah Provinsi">Pemerintah Provinsi</option>
                        <option value="Pemerintah Pusat">Pemerintah Pusat</option>
                        <option value="Pemerintah Kabupaten">Pemerintah Kabupaten</option>
                        <option value="Pemerintah Kota">Pemerintah Kota</option>
                        <option value="Swasta / Perusahaan">Swasta / Perusahaan</option>
                        <option value="Masyarakat / Yayasan">Masyarakat / Yayasan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- MAP -->
        <div class="reveal reveal-delay-2 mb-5">
            <div id="prasaranaMap"></div>
        </div>

        <!-- LIST HEADER -->
        <div class="d-flex justify-content-between align-items-end border-bottom border-secondary pb-2 mb-4 reveal reveal-delay-3" style="border-color: rgba(255,255,255,0.1) !important;">
            <h4 class="font-display fw-bold mb-0" style="color:var(--text-primary)"><i class="bi bi-grid-3x3-gap-fill me-2" style="color:var(--emerald)"></i>Daftar Prasarana</h4>
            <span class="text-secondary small font-tech" id="listCount">Memuat...</span>
        </div>

        <!-- LIST -->
        <div class="row reveal reveal-delay-3" id="prasaranaList">
            <div class="col-12 text-center text-muted py-5">
                <div class="spinner-border text-info mb-3" role="status"></div>
                <p>Mempersiapkan geospasial...</p>
            </div>
        </div>

    </div>
</section>

<!-- DETAIL MODAL -->
<div class="modal fade" id="prasaranaModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-display fw-bold d-flex align-items-center gap-2" id="modalPrasaranaTitle">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--emerald);display:flex;align-items:center;justify-content:center;color:var(--dark-bg);">
                        <i class="bi bi-building"></i>
                    </div>
                    Detail Prasarana
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalPrasaranaBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-info mb-3" role="status"></div>
                    <p>Memuat spesifikasi teknis...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let leafMap, markers = [];
let allData = [];
const allCaborOptions = @json($cabors);

// Light theme map tiles (Standard OpenStreetMap)
const MAP_TILES = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

document.addEventListener('DOMContentLoaded', () => {
    // Pindahkan modal ke body untuk mencegah masalah z-index backdrop
    document.body.appendChild(document.getElementById('prasaranaModal'));
    
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

    // Init Map
    leafMap = L.map('prasaranaMap').setView([-7.250445, 112.768845], 8);
    L.tileLayer(MAP_TILES, {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
        subdomains: 'abcd',
        maxZoom: 19
    }).addTo(leafMap);

    // Initial load
    loadData();

    // Event Listeners for Filters
    ['filterJenisP','filterCaborMap','filterLokasiMap','filterPengelola','filterKategoriP','filterStandarP'].forEach(id => {
        document.getElementById(id).addEventListener('change', loadData);
    });

    document.getElementById('filterJenisP').addEventListener('change', function() {
        const jenisId = this.value;
        loadKategoriByJenis(jenisId);
        loadCaborByJenis(jenisId);
    });
});

function loadKategoriByJenis(jenisId) {
    const sel = document.getElementById('filterKategoriP');
    sel.innerHTML = '<option value="">Semua Kategori</option>';
    
    if (jenisId == 1 || jenisId == 2) {
        sel.insertAdjacentHTML('beforeend', `
            <option value="Stadion">Stadion</option>
            <option value="GOR (Gedung Olahraga)">GOR</option>
            <option value="Lapangan Terbuka">Lapangan Terbuka</option>
            <option value="Kolam Renang">Kolam Renang</option>
            <option value="Sirkuit">Sirkuit</option>
            <option value="Sport Center">Sport Center</option>
            <option value="Fasilitas Fitnes">Fasilitas Fitnes</option>
        `);
    } else if (jenisId == 3) {
        sel.insertAdjacentHTML('beforeend', `
            <option value="Gedung Pemuda">Gedung Pemuda</option>
            <option value="Asrama Pemuda">Asrama Pemuda</option>
            <option value="Creative Hub">Creative Hub</option>
            <option value="Youth Center">Youth Center</option>
        `);
    } else if (jenisId == 4) {
        sel.insertAdjacentHTML('beforeend', `
            <option value="Bumi Perkemahan">Bumi Perkemahan</option>
            <option value="Gedung Kwarcab">Gedung Kwarcab</option>
            <option value="Pusdiklatcab">Pusdiklatcab</option>
        `);
    }
}

function loadCaborByJenis(jenisId) {
    const sel = document.getElementById('filterCaborMap');
    if (!jenisId || jenisId == 3 || jenisId == 4) {
        sel.innerHTML = '<option value="">Tidak tersedia</option>';
        sel.disabled = true;
        return;
    }

    sel.disabled = false;
    sel.innerHTML = '<option value="">Semua Cabor</option>';
    const jenisToTipe = {1:'olahraga_prestasi', 2:'olahraga_masyarakat'};
    const tipe = jenisToTipe[parseInt(jenisId)];
    
    if (tipe) {
        allCaborOptions.filter(c => c.tipe === tipe).forEach(c => {
            sel.insertAdjacentHTML('beforeend', `<option value="${c.id}">${c.nama}</option>`);
        });
    }
}

async function loadData() {
    const jenis  = document.getElementById('filterJenisP').value;
    const cabor  = document.getElementById('filterCaborMap').value;
    const lokasi = document.getElementById('filterLokasiMap').value;
    const kelola = document.getElementById('filterPengelola').value;
    const kategori = document.getElementById('filterKategoriP').value;
    const standar = document.getElementById('filterStandarP').value;

    let url = '/api/v1/public/prasarana?';
    if (jenis)  url += `jenis_id=${jenis}&`;
    if (cabor)  url += `cabor_id=${cabor}&`;
    if (lokasi) url += `lokasi_id=${lokasi}&`;
    if (kelola) url += `pengelola=${kelola}&`;
    if (kategori) url += `kategori=${encodeURIComponent(kategori)}&`;
    if (standar) url += `standar=${encodeURIComponent(standar)}&`;

    try {
        const r = await fetch(url);
        const d = await r.json();
        allData = d.data || d;

        document.getElementById('countPrasarana').textContent = allData.length;
        document.getElementById('listCount').textContent = `Menampilkan ${allData.length} prasarana terdaftar`;

        renderMarkers();
        renderCards();
    } catch(e) {
        document.getElementById('prasaranaList').innerHTML =
            '<div class="col-12 text-center text-danger py-5"><i class="bi bi-exclamation-triangle d-block mb-3" style="font-size:3rem"></i><p>Gagal memuat data geospasial</p></div>';
    }
}

function renderMarkers() {
    markers.forEach(m => leafMap.removeLayer(m));
    markers = [];

    const withCoords = allData.filter(p => p.latitude && p.longitude);

    withCoords.forEach(p => {
        const icon = L.divIcon({
            className: '',
            html: `<div style="background:var(--emerald);color:var(--dark-bg);border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;font-size:1rem;box-shadow:0 0 15px rgba(0,255,136,0.5);border:2px solid var(--dark-bg);"><i class="bi bi-geo-alt-fill"></i></div>`,
            iconSize:[32,32], iconAnchor:[16,32], popupAnchor:[0,-32]
        });

        const cabors = (p.cabors||[]).map(c=>c.nama).join(', ');
        const m = L.marker([p.latitude, p.longitude], {icon})
            .addTo(leafMap)
            .bindPopup(`
                <div style="min-width:200px">
                    <strong style="color:var(--tech-blue); font-size:1.05rem; display:block; margin-bottom:4px;">${p.nama}</strong>
                    <small style="color:var(--text-secondary); display:block; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:6px; margin-bottom:6px;">${p.lokasi?.name||''}</small>
                    ${cabors ? `<span style="font-size:.8rem; display:block; margin-bottom:4px; color:var(--text-primary)"><i class="bi bi-dribbble me-1 text-victory-gold"></i>${cabors}</span>` : ''}
                    <span style="font-size:.75rem; color:var(--text-secondary); display:block; margin-bottom:10px;"><i class="bi bi-person-badge me-1"></i>Pengelola: ${p.pengelola||'—'}</span>
                    <button onclick="showDetail(${p.id})" class="nav-cta border-0 rounded-pill w-100" style="font-size:.8rem; padding:6px 12px !important;">
                        Lihat Detail
                    </button>
                </div>
            `);
        markers.push(m);
    });

    if (withCoords.length > 0) {
        const group = L.featureGroup(markers);
        leafMap.fitBounds(group.getBounds().pad(0.1));
    }
}

function renderCards() {
    const container = document.getElementById('prasaranaList');

    if (!allData.length) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-5"><i class="bi bi-building-x d-block mb-3" style="font-size:3rem; opacity:0.5"></i><p>Tidak ada prasarana yang cocok dengan filter</p></div>';
        return;
    }

    container.innerHTML = allData.slice(0,12).map(p => {
        const foto  = p.fotos?.[0];
        const cabors= (p.cabors||[]).slice(0,3).map(c=>`<span class="badge" style="background:rgba(255,215,0,0.1);color:var(--victory-gold);border:1px solid rgba(255,215,0,0.2); font-size:.65rem; margin-right:4px;">${c.nama}</span>`).join('');

        return `
        <div class="col-md-4 col-sm-6 mb-4">
            <div class="prasarana-card" onclick="showDetail(${p.id})">
                ${foto
                    ? `<img src="/storage/${foto.foto}" class="foto-thumb mb-3" alt="${p.nama}" loading="lazy">`
                    : `<div class="foto-placeholder mb-3"><i class="bi bi-building"></i></div>`
                }
                <h5 class="fw-bold mb-1 font-display" style="color:var(--text-primary); font-size:1.1rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="${p.nama}">${p.nama}</h5>
                <div class="mb-2 d-flex gap-1 flex-wrap">
                    ${p.kategori ? `<span class="badge" style="background:rgba(0,212,255,0.1);color:var(--tech-blue);border:1px solid rgba(0,212,255,0.2);font-size:0.65rem">${p.kategori}</span>` : ''}
                    ${p.standar ? `<span class="badge" style="background:rgba(168,85,247,0.1);color:var(--neon-purple);border:1px solid rgba(168,85,247,0.2);font-size:0.65rem">${p.standar}</span>` : ''}
                </div>
                <p class="small mb-2" style="color:var(--text-secondary);">
                    <i class="bi bi-geo-alt-fill me-1" style="color:var(--emerald)"></i>${p.lokasi?.name||'—'}
                </p>
                <div class="mb-3">${cabors || '<span class="text-secondary small">—</span>'}</div>
                <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top" style="border-color:rgba(255,255,255,0.05) !important;">
                    <span class="badge" style="background:rgba(255,255,255,0.05); color:var(--text-secondary); border:1px solid rgba(255,255,255,0.1);">
                        <i class="bi bi-person me-1"></i>${p.pengelola||'—'}
                    </span>
                    ${p.latitude ? '<span class="small" style="color:var(--tech-blue)"><i class="bi bi-pin-map-fill"></i></span>' : ''}
                </div>
            </div>
        </div>`;
    }).join('');

    if (allData.length > 12) {
        container.insertAdjacentHTML('beforeend',`
        <div class="col-12 text-center mt-3">
            <p class="text-secondary" style="font-size:0.9rem;">Menampilkan 12 dari ${allData.length} prasarana. Gunakan filter untuk memperinci.</p>
        </div>`);
    }
}

async function showDetail(id) {
    let modalEl = document.getElementById('prasaranaModal');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    document.getElementById('modalPrasaranaBody').innerHTML = `
        <div class="text-center py-5 text-muted">
            <div class="spinner-border text-info mb-3" role="status"></div>
            <p>Memuat spesifikasi teknis...</p>
        </div>`;
    modal.show();

    try {
        let p = allData.find(x=>x.id===id);
        if (!p || !p.fasilitas) {
            const r = await fetch(`/api/v1/public/prasarana?prasarana_id=${id}`);
            if (r.ok) {
                const arr = await r.json();
                p = (arr.data||arr).find(x=>x.id===id) || p;
            }
        }
        
        try {
            const r2 = await fetch(`/api/v1/prasarana/${id}`);
            if (r2.ok) p = await r2.json();
        } catch(e) {}

        document.getElementById('modalPrasaranaTitle').innerHTML = `
            <div style="width:32px;height:32px;border-radius:8px;background:var(--emerald);display:flex;align-items:center;justify-content:center;color:var(--dark-bg);">
                <i class="bi bi-building"></i>
            </div>
            ${p.nama || 'Detail Prasarana'}
        `;

        const fotos    = p.fotos || [];
        const fasilitas= p.fasilitas || [];
        const cabors   = p.cabors || [];

        document.getElementById('modalPrasaranaBody').innerHTML = `
        <div class="row g-4 p-2">
            <div class="col-md-5">
                ${fotos.length
                    ? `<div id="fotoCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner rounded-4" style="border:1px solid rgba(255,255,255,0.1)">
                            ${fotos.map((f,i)=>`
                            <div class="carousel-item ${i===0?'active':''}">
                                <img src="/storage/${f.foto}" class="d-block w-100" style="height:300px;object-fit:cover;" alt="">
                                ${f.deskripsi?`<div class="carousel-caption d-none d-md-block" style="background:rgba(0,0,0,0.5); backdrop-filter:blur(4px); border-radius:8px; bottom:10px; padding:8px;"><small>${f.deskripsi}</small></div>`:''}
                            </div>`).join('')}
                        </div>
                        ${fotos.length>1?`
                        <button class="carousel-control-prev" type="button" data-bs-target="#fotoCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" style="filter:invert(1)"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#fotoCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" style="filter:invert(1)"></span>
                        </button>`:''}
                    </div>`
                    : `<div class="foto-placeholder rounded-4" style="height:300px"><i class="bi bi-building" style="font-size:4rem"></i></div>`
                }
            </div>
            <div class="col-md-7">
                <h4 class="fw-bold font-display" style="color:var(--text-primary)">${p.nama||'—'}</h4>
                <p class="mb-4" style="color:var(--text-secondary);"><i class="bi bi-geo-alt-fill me-2" style="color:var(--emerald)"></i>${p.lokasi?.name||'—'}</p>

                <div class="row g-3 mb-4">
                    <div class="col-6">
                        <div class="p-3 bg-light-dark rounded-4 h-100">
                            <div class="small mb-1" style="color:var(--text-secondary)">Pengelola</div>
                            <div class="fw-bold" style="font-size:.9rem; color:var(--tech-blue)">${p.pengelola||'—'}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light-dark rounded-4 h-100">
                            <div class="small mb-1" style="color:var(--text-secondary)">Narahubung</div>
                            <div class="fw-bold" style="font-size:.9rem; color:var(--text-primary)">${p.narahubung||'—'}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light-dark rounded-4 h-100">
                            <div class="small mb-1" style="color:var(--text-secondary)">Kategori</div>
                            <div class="fw-bold" style="font-size:.9rem; color:var(--text-primary)">${p.kategori||'—'}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 bg-light-dark rounded-4 h-100">
                            <div class="small mb-1" style="color:var(--text-secondary)">Standar</div>
                            <div class="fw-bold" style="font-size:.9rem; color:var(--text-primary)">${p.standar||'—'}</div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-light-dark rounded-4 h-100">
                            <div class="small mb-1" style="color:var(--text-secondary)">Alamat Lengkap</div>
                            <div class="fw-bold small" style="color:var(--text-primary); line-height:1.6;">${p.alamat||'—'}</div>
                        </div>
                    </div>
                </div>

                ${cabors.length ? `
                <div class="mb-4">
                    <div class="section-label mb-2" style="font-size:0.65rem;">Cabor Tersedia</div>
                    <div class="d-flex flex-wrap gap-2">
                        ${cabors.map(c=>`<span class="badge" style="background:rgba(255,215,0,0.1);color:var(--victory-gold);border:1px solid rgba(255,215,0,0.2); padding:6px 12px;">${c.nama}</span>`).join('')}
                    </div>
                </div>` : ''}

                ${p.keterangan ? `
                <div class="mb-4">
                    <div class="section-label mb-2" style="font-size:0.65rem;">Deskripsi</div>
                    <p class="small" style="color:var(--text-secondary); line-height:1.6;">${p.keterangan}</p>
                </div>` : ''}

                ${p.latitude && p.longitude ? `
                <a href="https://www.google.com/maps?q=${p.latitude},${p.longitude}" target="_blank" rel="noopener noreferrer" class="btn-gmaps mt-2">
                    <i class="bi bi-geo-alt-fill"></i>
                    Buka di Google Maps
                    <i class="bi bi-box-arrow-up-right ms-1" style="font-size:.75rem"></i>
                </a>` : ''}
            </div>

            ${fasilitas.length ? `
            <div class="col-12 mt-4 pt-4 border-top" style="border-color:rgba(255,255,255,0.05) !important;">
                <div class="section-label mb-3">Daftar Fasilitas Detail</div>
                <div class="table-responsive rounded-3 border" style="border-color:rgba(255,255,255,0.1) !important;">
                    <table class="table table-sm table-dark-glass mb-0">
                        <thead>
                            <tr><th class="py-2 px-3">Fasilitas</th><th class="py-2 px-3">Jml</th><th class="py-2 px-3">Kondisi</th><th class="py-2 px-3">Keterangan</th></tr>
                        </thead>
                        <tbody>
                            ${fasilitas.map(f=>`
                            <tr>
                                <td class="py-2 px-3 fw-bold">${f.nama}</td>
                                <td class="py-2 px-3 text-center">${f.jumlah||1}</td>
                                <td class="py-2 px-3">
                                    <span class="badge ${f.kondisi==='Baik'?'bg-success text-white':f.kondisi==='Rusak Ringan'?'bg-warning text-dark':'bg-danger text-white'}">${f.kondisi||'Baik'}</span>
                                </td>
                                <td class="py-2 px-3 text-secondary small">${f.keterangan||'—'}</td>
                            </tr>`).join('')}
                        </tbody>
                    </table>
                </div>
            </div>` : ''}

            ${p.latitude&&p.longitude ? `
            <div class="col-12 mt-4 pt-4 border-top" style="border-color:rgba(255,255,255,0.05) !important;">
                <div class="section-label mb-3">Visualisasi Lokasi</div>
                <div id="detailMap" style="height:250px;border-radius:16px;border:1px solid rgba(255,255,255,0.1);"></div>
            </div>` : ''}
        </div>`;

        if (p.latitude && p.longitude) {
            setTimeout(() => {
                if (window.detailMapInstance) {
                    window.detailMapInstance.remove();
                }
                const dm = L.map('detailMap').setView([p.latitude, p.longitude], 15);
                window.detailMapInstance = dm;
                
                L.tileLayer(MAP_TILES, {
                    attribution: '&copy; OpenStreetMap & CARTO'
                }).addTo(dm);
                
                const icon = L.divIcon({
                    className: '',
                    html: `<div style="background:var(--tech-blue);color:var(--dark-bg);border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;font-size:0.8rem;box-shadow:0 0 10px rgba(0,212,255,0.5);border:2px solid var(--dark-bg);"><i class="bi bi-circle-fill"></i></div>`,
                    iconSize:[24,24], iconAnchor:[12,24], popupAnchor:[0,-24]
                });
                
                L.marker([p.latitude, p.longitude], {icon})
                    .addTo(dm)
                    .bindPopup(`<strong style="color:var(--text-primary)">${p.nama}</strong>`)
                    .openPopup();
            }, 300);
        }
    } catch(e) {
        document.getElementById('modalPrasaranaBody').innerHTML = `
            <div class="text-danger text-center p-5">
                <i class="bi bi-wifi-off d-block mb-3" style="font-size:3rem"></i>
                <h5>Gagal Memuat Detail</h5>
            </div>`;
    }
}
</script>
@endpush
