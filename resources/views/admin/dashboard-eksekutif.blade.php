@extends('layouts.admin')
@section('title', 'Dashboard Eksekutif')

@push('styles')
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<style>
/* CSS Reset & Vars */
:root {
    --glass-bg: rgba(255, 255, 255, 0.85);
    --glass-border: rgba(255, 255, 255, 0.2);
    --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
}

.dashboard-bg {
    background-color: #f8f9fa;
    background-image: radial-gradient(circle at 100% 0%, #e2ebf0 0%, #f8f9fa 100%);
}

.glass-card {
    background: var(--glass-bg);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border);
    border-radius: 1rem;
    box-shadow: var(--glass-shadow);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.glass-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.1);
}

.stat-card-inner { border-left: 4px solid transparent; }
.stat-card-primary { border-left-color: #0d6efd; }
.stat-card-info { border-left-color: #0dcaf0; }
.stat-card-warning { border-left-color: #ffc107; }
.stat-card-success { border-left-color: #198754; }

.table-hover tbody tr { transition: background-color 0.2s, transform 0.2s; cursor: pointer; }
.table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.05); transform: scale(1.01); }
.transition-hover { transition: transform 0.2s ease; }
.transition-hover:hover { transform: translateY(-3px); }

.scrollable-section {
    max-height: 400px;
    overflow-y: auto;
}
.scrollable-section::-webkit-scrollbar { width: 6px; }
.scrollable-section::-webkit-scrollbar-track { background: rgba(0,0,0,0.05); border-radius: 10px; }
.scrollable-section::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.15); border-radius: 10px; }

/* Skeleton Loader */
.skeleton-box {
    display: inline-block;
    height: 1em;
    position: relative;
    overflow: hidden;
    background-color: #e2e5e7;
    border-radius: 4px;
    width: 100%;
}
.skeleton-box::after {
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    transform: translateX(-100%);
    background-image: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0,
        rgba(255, 255, 255, 0.5) 20%,
        rgba(255, 255, 255, 0) 60%,
        rgba(255, 255, 255, 0)
    );
    animation: shimmer 1.5s infinite;
    content: '';
}
@keyframes shimmer {
    100% { transform: translateX(100%); }
}

.loading-overlay {
    position: relative;
}
.loading-overlay::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.6);
    z-index: 10;
    border-radius: 1rem;
    backdrop-filter: blur(2px);
    display: none;
}
.loading-overlay.is-loading::before {
    display: block;
}
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-4 dashboard-bg">
    <!-- Welcome Banner -->
    <div class="row mb-4" data-aos="fade-down">
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #4a90e2 100%);">
                <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">Selamat Datang, {{ $user->name }}</h4>
                        <p class="mb-0 text-white-50">
                            Role: <span class="badge bg-white text-primary">{{ $user->roles->first()->name ?? 'Eksekutif' }}</span>
                            @if($user->domisili) | Wilayah: {{ $user->domisili->name }} @else | Wilayah: Provinsi Jawa Timur @endif
                        </p>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <select id="filter_tahun_global" class="form-select form-select-sm bg-white text-dark border-0 shadow-sm" style="width: 120px; transition: all 0.3s;">
                            <option value="">Semua Tahun</option>
                            <option value="2026" selected>2026</option>
                            <option value="2025">2025</option>
                            <option value="2024">2024</option>
                        </select>
                        @if(!$user->kab_kota_id)
                        <select id="filter_kab_kota_global" class="form-select form-select-sm bg-white text-dark border-0 shadow-sm" style="width: 150px;">
                            <option value="">Semua Wilayah</option>
                        </select>
                        @endif
                        @if(!$user->jenis_id)
                        <select id="filter_jenis_global" class="form-select form-select-sm bg-white text-dark border-0 shadow-sm" style="width: 150px;">
                            <option value="">Semua Bidang</option>
                        </select>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-4 mb-4" id="statsContainer" data-aos="fade-up" data-aos-delay="100">
        <!-- Skeleton -->
        @for($i=0; $i<4; $i++)
        <div class="col-md-3 col-6 skeleton-stat"><div class="glass-card p-3"><div class="skeleton-box" style="height: 60px;"></div></div></div>
        @endfor
    </div>

    @php
        $showPrestasi = ($user->jenis_id == 1 || $user->jenis_id == 2 || $user->hasRole('Kepala Dinas Provinsi') || $user->hasRole('Kepala Dinas Kab/Kota'));
    @endphp

    @if($showPrestasi)
    <!-- Prestasi Atlet Section -->
    <div class="card glass-card mb-4 loading-overlay" id="prestasiSection" data-aos="fade-up" data-aos-delay="200">
        <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-trophy-fill text-warning me-2"></i>Prestasi Atlet</h5>
            <div class="d-flex gap-2">
                <select id="filter_skala_prestasi" class="form-select form-select-sm bg-light border-0">
                    <option value="">Semua Skala</option>
                </select>
                @if(!$user->cabor_id)
                <select id="filter_cabor_prestasi" class="form-select form-select-sm bg-light border-0" style="width: 140px;">
                    <option value="">Semua Cabor</option>
                </select>
                @endif
                <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="filter_difabel_prestasi" 
                        {{ $user->hasRole('Ketua NPCI Provinsi') || $user->hasRole('Ketua NPCI Kab/Kota') ? 'checked disabled' : '' }}>
                    <label class="form-check-label text-muted small" for="filter_difabel_prestasi">Hanya Difabel</label>
                </div>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="p-3 bg-light rounded-4 text-center h-100 d-flex flex-column justify-content-center" style="box-shadow: inset 0 2px 10px rgba(0,0,0,0.02);">
                        <h6 class="text-muted fw-bold mb-3">REKAP MEDALI</h6>
                        <div class="d-flex justify-content-around mb-2">
                            <div data-aos="zoom-in" data-aos-delay="300"><div class="fs-4" style="filter: drop-shadow(0 4px 6px rgba(255,215,0,0.4));">🥇</div><h4 class="fw-bold text-dark mb-0" id="medali_emas"><div class="skeleton-box w-50"></div></h4></div>
                            <div data-aos="zoom-in" data-aos-delay="400"><div class="fs-4" style="filter: drop-shadow(0 4px 6px rgba(192,192,192,0.4));">🥈</div><h4 class="fw-bold text-dark mb-0" id="medali_perak"><div class="skeleton-box w-50"></div></h4></div>
                            <div data-aos="zoom-in" data-aos-delay="500"><div class="fs-4" style="filter: drop-shadow(0 4px 6px rgba(205,127,50,0.4));">🥉</div><h4 class="fw-bold text-dark mb-0" id="medali_perunggu"><div class="skeleton-box w-50"></div></h4></div>
                        </div>
                        <hr class="text-muted opacity-25 my-2">
                        <h6 class="fw-bold text-primary mb-0">Total: <span id="medali_total"><div class="skeleton-box w-25 d-inline-block"></div></span></h6>
                    </div>
                </div>
                <div class="col-md-9">
                    <div style="height: 250px;"><canvas id="chartMedaliCabor"></canvas></div>
                </div>
            </div>
            
            <div class="mt-4" id="trendContainer" style="display: none;">
                <h6 class="fw-bold text-muted mb-3">Trend Perolehan Medali Tahunan</h6>
                <div style="height: 200px;"><canvas id="chartTrendMedali"></canvas></div>
            </div>

            <div class="mt-4">
                <h6 class="fw-bold text-muted mb-3">Top 10 Atlet Berprestasi <span class="small text-muted fw-normal">(Klik baris untuk riwayat detail)</span></h6>
                <div class="table-responsive scrollable-section">
                    <table class="table table-borderless table-hover align-middle">
                        <thead class="bg-light text-muted small rounded-3" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th>Nama Atlet</th>
                                <th>Cabor</th>
                                <th>Partisipasi</th>
                                <th>Medali (🥇🥈🥉)</th>
                                <th>Skor</th>
                            </tr>
                        </thead>
                        <tbody id="topAtletList">
                            @for($i=0; $i<3; $i++)
                            <tr><td colspan="5"><div class="skeleton-box"></div></td></tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row g-4 mb-4">
        <!-- Ringkasan SDM -->
        <div class="col-lg-8" data-aos="fade-right">
            <div class="card glass-card h-100 loading-overlay" id="sdmSection">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people-fill text-primary me-2"></i>Ringkasan SDM</h5>
                    <select id="filter_peran_sdm" class="form-select form-select-sm bg-light border-0 w-auto shadow-sm">
                        <option value="">Semua Peran</option>
                        <option value="Atlet">Atlet</option>
                        <option value="Pelatih">Pelatih</option>
                        <option value="Wasit/Juri">Wasit/Juri</option>
                    </select>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-5">
                            <div style="height: 250px;"><canvas id="chartUsia"></canvas></div>
                        </div>
                        <div class="col-md-7 scrollable-section">
                            <div class="row g-3" id="sdmInfoContainer">
                                <div class="col-12"><div class="skeleton-box" style="height:80px;"></div></div>
                            </div>
                            <div class="mt-4 p-3 bg-light rounded-4 border">
                                <h6 class="fw-bold text-muted small mb-3">Distribusi Gender</h6>
                                <div class="progress shadow-sm" style="height: 25px; border-radius: 12px; overflow: hidden;">
                                    <div class="progress-bar bg-primary" id="barGenderL" style="width: 50%; font-weight: bold;">L</div>
                                    <div class="progress-bar" id="barGenderP" style="width: 50%; background-color: #e83e8c; font-weight: bold;">P</div>
                                </div>
                                <div class="d-flex justify-content-between small fw-bold mt-2">
                                    <span class="text-primary" id="labelGenderL"><div class="skeleton-box" style="width:80px;"></div></span>
                                    <span style="color: #e83e8c;" id="labelGenderP"><div class="skeleton-box" style="width:80px;"></div></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Event Terkini -->
        <div class="col-lg-4" data-aos="fade-left">
            <div class="card glass-card h-100 loading-overlay" id="eventSection">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0 text-dark" id="eventSectionTitle"><i class="bi bi-calendar-event-fill text-info me-2"></i>Event Terkini</h5>
                </div>
                <div class="card-body p-4 pt-2 scrollable-section" style="max-height: 600px;">
                    <div class="list-group list-group-flush gap-2" id="eventList">
                        @for($i=0; $i<4; $i++)
                        <div class="list-group-item px-3 border-0 bg-light rounded-3"><div class="skeleton-box mb-2"></div><div class="skeleton-box w-50"></div></div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Distribusi Lanjutan -->
    <div class="row g-4" data-aos="fade-up">
        <div class="col-md-6">
             <div class="card glass-card h-100 loading-overlay" id="caborSection">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted mb-3">Top 10 Cabor (Berdasarkan Jumlah SDM)</h6>
                    <div style="height: 320px;"><canvas id="chartTopCabor"></canvas></div>
                </div>
             </div>
        </div>
        <div class="col-md-6">
             <div class="card glass-card h-100 loading-overlay" id="kabkotaSection">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted mb-3">Distribusi SDM per Wilayah (Top 10)</h6>
                    <div style="height: 320px;"><canvas id="chartKabKota"></canvas></div>
                </div>
             </div>
        </div>
    </div>
</div>

<!-- Modal Riwayat Atlet -->
<div class="modal fade" id="modalRiwayatAtlet" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 rounded-4" style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
      <div class="modal-header border-0 bg-primary bg-opacity-10">
        <h5 class="modal-title fw-bold text-primary">
            <i class="bi bi-person-lines-fill me-2"></i> Riwayat Prestasi Atlet
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4 position-relative">
          <div id="modalLoading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status"></div>
              <p class="text-muted mt-2">Memuat riwayat...</p>
          </div>
          <div id="modalContent" style="display:none;">
              <div class="d-flex flex-wrap gap-3 mb-4 bg-white p-3 rounded-4 shadow-sm border">
                  <div><small class="text-muted d-block">Nama Lengkap</small><strong class="fs-5" id="mr_nama"></strong></div>
                  <div class="ms-auto text-end">
                      <span class="badge bg-info bg-opacity-10 text-info fs-6" id="mr_gender"></span>
                      <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6" id="mr_umur"></span>
                  </div>
              </div>
              
              <h6 class="fw-bold text-muted mb-3">Timeline Event & Prestasi</h6>
              <div class="position-relative border-start border-2 border-primary ms-3" id="mr_timeline" style="padding-left: 20px;">
                  <!-- Injected via JS -->
              </div>
          </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
AOS.init({ once: true, offset: 50, duration: 800 });

document.addEventListener('DOMContentLoaded', function() {
    // Dropdown Data Population (?all=1 added)
    fetch('/api/v1/kab-kota?all=1').then(r=>r.json()).then(d => {
        let sel = document.getElementById('filter_kab_kota_global');
        if(sel) d.forEach(k => sel.add(new Option(k.name, k.id)));
    });
    fetch('/api/v1/jenis?all=1').then(r=>r.json()).then(d => {
        let sel = document.getElementById('filter_jenis_global');
        if(sel) d.forEach(j => sel.add(new Option(j.nama, j.id)));
    });
    fetch('/api/v1/cabor?all=1').then(r=>r.json()).then(d => {
        let sel = document.getElementById('filter_cabor_prestasi');
        if(sel) d.forEach(c => sel.add(new Option(c.nama, c.id)));
    });
    fetch('/api/v1/skala?all=1').then(r=>r.json()).then(d => {
        let sel = document.getElementById('filter_skala_prestasi');
        if(sel) d.forEach(s => sel.add(new Option(s.nama, s.id)));
    });

    let charts = {};
    const modalRiwayat = new bootstrap.Modal(document.getElementById('modalRiwayatAtlet'));
    
    function getGlobalFilters() {
        let f = {};
        let t = document.getElementById('filter_tahun_global'); if(t && t.value) f.tahun = t.value;
        let k = document.getElementById('filter_kab_kota_global'); if(k && k.value) f.kab_kota_id = k.value;
        let j = document.getElementById('filter_jenis_global'); if(j && j.value) f.jenis_id = j.value;
        return f;
    }

    function buildQuery(params) {
        let parts = [];
        for(let key in params) {
            if(params[key]) parts.push(encodeURIComponent(key) + '=' + encodeURIComponent(params[key]));
        }
        return parts.length ? '?' + parts.join('&') : '';
    }

    function toggleLoading(sectionId, state) {
        let el = document.getElementById(sectionId);
        if(el) {
            if(state) el.classList.add('is-loading');
            else el.classList.remove('is-loading');
        }
    }

    function loadStats() {
        let container = document.getElementById('statsContainer');
        let isFirstLoad = container.querySelector('.skeleton-stat') !== null;
        if(!isFirstLoad) container.style.opacity = 0.5;

        fetch('/api/v1/dashboard/stats' + buildQuery(getGlobalFilters()))
            .then(r=>r.json()).then(d => {
                container.style.opacity = 1;
                container.innerHTML = `
                    <div class="col-md-3 col-6"><a href="/admin/orang" class="text-decoration-none"><div class="card glass-card stat-card-inner stat-card-primary h-100 transition-hover"><div class="card-body p-3 d-flex align-items-center gap-3"><div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle" style="box-shadow: inset 0 2px 5px rgba(0,0,0,0.05);"><i class="bi bi-people-fill fs-4"></i></div><div><h6 class="text-muted small fw-bold mb-1">Total SDM</h6><h3 class="fw-bold mb-0 text-dark">${d.total_orang.toLocaleString()}</h3></div></div></div></a></div>
                    <div class="col-md-3 col-6"><a href="/admin/events" class="text-decoration-none"><div class="card glass-card stat-card-inner stat-card-info h-100 transition-hover"><div class="card-body p-3 d-flex align-items-center gap-3"><div class="bg-info bg-opacity-10 text-info p-3 rounded-circle"><i class="bi bi-calendar-event-fill fs-4"></i></div><div><h6 class="text-muted small fw-bold mb-1">Total Event</h6><h3 class="fw-bold mb-0 text-dark">${d.total_event.toLocaleString()}</h3></div></div></div></a></div>
                    <div class="col-md-3 col-6"><a href="/admin/prasarana" class="text-decoration-none"><div class="card glass-card stat-card-inner stat-card-warning h-100 transition-hover"><div class="card-body p-3 d-flex align-items-center gap-3"><div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle"><i class="bi bi-geo-alt-fill fs-4"></i></div><div><h6 class="text-muted small fw-bold mb-1">Prasarana</h6><h3 class="fw-bold mb-0 text-dark">${d.total_prasarana.toLocaleString()}</h3></div></div></div></a></div>
                    <div class="col-md-3 col-6"><a href="/admin/organisasi" class="text-decoration-none"><div class="card glass-card stat-card-inner stat-card-success h-100 transition-hover"><div class="card-body p-3 d-flex align-items-center gap-3"><div class="bg-success bg-opacity-10 text-success p-3 rounded-circle"><i class="bi bi-building fs-4"></i></div><div><h6 class="text-muted small fw-bold mb-1">Organisasi</h6><h3 class="fw-bold mb-0 text-dark">${d.total_organisasi.toLocaleString()}</h3></div></div></div></a></div>
                `;
            });
    }

    window.showAtletRiwayat = function(orang_id) {
        modalRiwayat.show();
        document.getElementById('modalLoading').style.display = 'block';
        document.getElementById('modalContent').style.display = 'none';

        let f = getGlobalFilters();
        let s = document.getElementById('filter_skala_prestasi'); if(s && s.value) f.skala_id = s.value;
        let c = document.getElementById('filter_cabor_prestasi'); if(c && c.value) f.cabor_id = c.value;
        
        fetch('/api/v1/dashboard/atlet-riwayat/' + orang_id + buildQuery(f)).then(r=>r.json()).then(data => {
            document.getElementById('modalLoading').style.display = 'none';
            document.getElementById('modalContent').style.display = 'block';
            
            document.getElementById('mr_nama').innerText = data.atlet.nama;
            document.getElementById('mr_gender').innerText = data.atlet.gender;
            document.getElementById('mr_umur').innerText = (data.atlet.umur ?? '-') + ' Tahun';

            let tHtml = '';
            if(data.riwayat.length === 0) {
                tHtml = '<div class="text-muted">Tidak ada riwayat medali di event terpilih.</div>';
            } else {
                tHtml = data.riwayat.map(r => {
                    let medalBadge = '';
                    let m = r.medali.toLowerCase();
                    if(m==='emas') medalBadge='<span class="badge rounded-pill bg-warning text-dark px-3 py-2 border shadow-sm">🥇 Emas</span>';
                    else if(m==='perak') medalBadge='<span class="badge rounded-pill bg-light text-dark px-3 py-2 border shadow-sm">🥈 Perak</span>';
                    else if(m==='perunggu') medalBadge='<span class="badge rounded-pill" style="background-color: #cd7f32; color: #fff; padding: 0.5rem 1rem;">🥉 Perunggu</span>';
                    
                    return `
                    <div class="mb-4 position-relative">
                        <div class="position-absolute rounded-circle bg-primary" style="width: 12px; height: 12px; left: -26px; top: 6px;"></div>
                        <div class="d-flex justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold mb-1 text-dark">${r.event_nama} <span class="text-muted fw-normal">${r.event_tahun ? '('+r.event_tahun+')' : ''}</span></h6>
                                <div class="small text-muted mb-2">
                                    <i class="bi bi-tag-fill me-1"></i> ${r.cabor} &bull; 
                                    <i class="bi bi-bookmarks-fill me-1"></i> ${r.kategori ? r.kategori : 'Kategori/Nomor -'} &bull; 
                                    <i class="bi bi-layers-fill me-1"></i> Skala ${r.skala}
                                </div>
                                <div class="fw-medium">${r.prestasi}</div>
                            </div>
                            <div class="mt-2 mt-sm-0">${medalBadge}</div>
                        </div>
                    </div>`;
                }).join('');
            }
            document.getElementById('mr_timeline').innerHTML = tHtml;
        }).catch(() => {
            document.getElementById('modalLoading').innerHTML = '<div class="text-danger"><i class="bi bi-exclamation-triangle"></i> Gagal mengambil data.</div>';
        });
    };

    function loadPrestasi() {
        toggleLoading('prestasiSection', true);
        let f = getGlobalFilters();
        let c = document.getElementById('filter_cabor_prestasi'); if(c && c.value) f.cabor_id = c.value;
        let s = document.getElementById('filter_skala_prestasi'); if(s && s.value) f.skala_id = s.value;
        let d = document.getElementById('filter_difabel_prestasi'); if(d && d.checked) f.disabilitas = true;

        fetch('/api/v1/dashboard/prestasi' + buildQuery(f)).then(r=>r.json()).then(data => {
            document.getElementById('medali_emas').innerText = data.rekap_medali.emas;
            document.getElementById('medali_perak').innerText = data.rekap_medali.perak;
            document.getElementById('medali_perunggu').innerText = data.rekap_medali.perunggu;
            document.getElementById('medali_total').innerText = data.rekap_medali.total;

            let tbody = document.getElementById('topAtletList');
            if (tbody) {
                tbody.innerHTML = data.top_atlet.map(a => `
                    <tr onclick="showAtletRiwayat(${a.orang_id})">
                        <td><div class="fw-bold text-primary">${a.nama}</div></td>
                        <td><span class="badge bg-light text-secondary border">${a.cabor}</span></td>
                        <td class="text-muted small">${a.total_event} Event</td>
                        <td class="fw-medium">
                            ${a.emas>0 ? '🥇'+a.emas+' ' : ''}
                            ${a.perak>0 ? '🥈'+a.perak+' ' : ''}
                            ${a.perunggu>0 ? '🥉'+a.perunggu : ''}
                        </td>
                        <td><span class="badge bg-primary shadow-sm">${a.skor} Pts</span></td>
                    </tr>
                `).join('');
                if(data.top_atlet.length === 0) tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><i class="bi bi-inbox fs-3 d-block text-black-50 mb-2"></i>Tidak ada data atlet berprestasi.</td></tr>';
            }

            // Enhanced Chart Medali per Cabor
            let ctxC = document.getElementById('chartMedaliCabor');
            if(ctxC) {
                if(charts.medali) charts.medali.destroy();
                let delayed;
                charts.medali = new Chart(ctxC, {
                    type: 'bar',
                    data: {
                        labels: data.medali_per_cabor.map(m => m.cabor),
                        datasets: [
                            { label: 'Emas', data: data.medali_per_cabor.map(m => m.emas), backgroundColor: 'rgba(255, 215, 0, 0.8)', borderColor: '#FFD700', borderWidth: 1, borderRadius: 4, borderSkipped: false },
                            { label: 'Perak', data: data.medali_per_cabor.map(m => m.perak), backgroundColor: 'rgba(192, 192, 192, 0.8)', borderColor: '#C0C0C0', borderWidth: 1, borderRadius: 4, borderSkipped: false },
                            { label: 'Perunggu', data: data.medali_per_cabor.map(m => m.perunggu), backgroundColor: 'rgba(205, 127, 50, 0.8)', borderColor: '#CD7F32', borderWidth: 1, borderRadius: 4, borderSkipped: false }
                        ]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false,
                        animation: {
                            onComplete: () => { delayed = true; },
                            delay: (context) => {
                                let delay = 0;
                                if (context.type === 'data' && context.mode === 'default' && !delayed) {
                                    delay = context.dataIndex * 150 + context.datasetIndex * 50;
                                }
                                return delay;
                            },
                        },
                        scales: { 
                            x: { stacked: false, grid: {display: false} }, 
                            y: { stacked: false, border: {display: false}, beginAtZero: true } 
                        },
                        plugins: { tooltip: { mode: 'index', intersect: false, padding: 12, cornerRadius: 8, backgroundColor: 'rgba(0,0,0,0.8)' } },
                        interaction: { mode: 'nearest', axis: 'x', intersect: false }
                    }
                });
            }

            toggleLoading('prestasiSection', false);
        }).catch(()=>toggleLoading('prestasiSection', false));
    }

    function loadSdm() {
        toggleLoading('sdmSection', true);
        let f = getGlobalFilters();
        let p = document.getElementById('filter_peran_sdm'); if(p && p.value) f.peran = p.value;
        let d = document.getElementById('filter_difabel_prestasi'); if(d && d.checked) f.disabilitas = true;
        
        fetch('/api/v1/dashboard/sdm-ringkasan' + buildQuery(f)).then(r=>r.json()).then(data => {
            document.getElementById('sdmInfoContainer').innerHTML = `
                <div class="col-6"><div class="p-3 bg-white rounded-4 border shadow-sm text-center transition-hover"><div class="text-primary fw-bold small text-uppercase tracking-wide">Total Data</div><div class="fs-2 fw-bold text-dark mt-2">${data.total.toLocaleString()}</div></div></div>
                <div class="col-6">
                    <div class="d-flex flex-column gap-2 h-100">
                        <div class="p-2 bg-primary bg-opacity-10 rounded-3 d-flex justify-content-between align-items-center border border-primary border-opacity-10">
                            <div class="small fw-bold text-primary"><i class="bi bi-gender-male me-1"></i> Laki-laki</div>
                            <div class="fw-bold">${data.per_gender.L.toLocaleString()}</div>
                        </div>
                        <div class="p-2 rounded-3 d-flex justify-content-between align-items-center border" style="background-color: rgba(232, 62, 140, 0.1); border-color: rgba(232, 62, 140, 0.2) !important;">
                            <div class="small fw-bold" style="color: #e83e8c;"><i class="bi bi-gender-female me-1"></i> Perempuan</div>
                            <div class="fw-bold" style="color: #d63384;">${data.per_gender.P.toLocaleString()}</div>
                        </div>
                    </div>
                </div>
            `;
            
            let totalG = data.per_gender.L + data.per_gender.P;
            let pctL = totalG ? Math.round((data.per_gender.L/totalG)*100) : 0;
            let pctP = totalG ? Math.round((data.per_gender.P/totalG)*100) : 0;
            
            document.getElementById('barGenderL').style.width = pctL + '%';
            document.getElementById('barGenderL').innerText = pctL > 5 ? pctL + '%' : '';
            document.getElementById('barGenderP').style.width = pctP + '%';
            document.getElementById('barGenderP').innerText = pctP > 5 ? pctP + '%' : '';
            
            document.getElementById('labelGenderL').innerText = `${data.per_gender.L.toLocaleString()} Laki-laki`;
            document.getElementById('labelGenderP').innerText = `${data.per_gender.P.toLocaleString()} Perempuan`;

            let ctxU = document.getElementById('chartUsia');
            if(ctxU) {
                if(charts.usia) charts.usia.destroy();
                // Custom color palette for ages
                const ageColors = ['#0dcaf0', '#0d6efd', '#6610f2', '#6c757d'];
                charts.usia = new Chart(ctxU, {
                    type: 'doughnut',
                    data: {
                        labels: data.per_usia.map(u => u.range + ' Tahun'),
                        datasets: [{ 
                            label: 'Jumlah', 
                            data: data.per_usia.map(u => u.total), 
                            backgroundColor: ageColors, 
                            borderWidth: 2,
                            hoverOffset: 4
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        cutout: '65%',
                        plugins: { 
                            legend: {position: 'bottom', labels: {boxWidth: 12, padding: 15}} 
                        } 
                    }
                });
            }
            toggleLoading('sdmSection', false);
        }).catch(()=>toggleLoading('sdmSection', false));
    }

    function loadCharts() {
        toggleLoading('caborSection', true);
        toggleLoading('kabkotaSection', true);
        
        let f = getGlobalFilters();
        let d = document.getElementById('filter_difabel_prestasi'); if(d && d.checked) f.disabilitas = true;
        
        fetch('/api/v1/dashboard/charts' + buildQuery(f)).then(r=>r.json()).then(data => {
            let ctxCb = document.getElementById('chartTopCabor');
            if(ctxCb && data.top_cabor) {
                if(charts.cabor) charts.cabor.destroy();
                let vibrantColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#E7E9ED', '#8AC926', '#1982C4', '#6A4C93'];
                charts.cabor = new Chart(ctxCb, {
                    type: 'polarArea',
                    data: {
                        labels: data.top_cabor.map(c => c.nama.length > 15 ? c.nama.substring(0,15)+'...' : c.nama),
                        datasets: [{ 
                            label: 'SDM', 
                            data: data.top_cabor.map(c => c.total), 
                            backgroundColor: vibrantColors.map(c => c + 'CC'),
                            borderColor: '#ffffff', 
                            borderWidth: 2 
                        }]
                    },
                    options: { 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        animation: { animateRotate: true, animateScale: true, duration: 2000, easing: 'easeOutQuart' },
                        plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } },
                    }
                });
            }
            toggleLoading('caborSection', false);
            
            let ctxKb = document.getElementById('chartKabKota');
            if(ctxKb && data.per_kab_kota) {
                if(charts.kab) charts.kab.destroy();
                let delayed;
                charts.kab = new Chart(ctxKb, {
                    type: 'bar',
                    data: {
                        labels: data.per_kab_kota.map(k => k.nama.replace('Kabupaten ','Kab. ')),
                        datasets: [{ 
                            label: 'Jumlah SDM', 
                            data: data.per_kab_kota.map(k => k.total), 
                            backgroundColor: 'rgba(25, 135, 84, 0.8)', 
                            borderColor: '#198754', 
                            borderWidth: 1, 
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: { 
                        indexAxis: 'x', 
                        responsive: true, 
                        maintainAspectRatio: false, 
                        animation: {
                            onComplete: () => { delayed = true; },
                            delay: (context) => {
                                let delay = 0;
                                if (context.type === 'data' && context.mode === 'default' && !delayed) {
                                    delay = context.dataIndex * 150;
                                }
                                return delay;
                            },
                        },
                        plugins: { 
                            legend: {display: false},
                            tooltip: { padding: 12, cornerRadius: 8, backgroundColor: 'rgba(0,0,0,0.8)' }
                        }, 
                        scales: {x: {grid: {display: false}}, y: {grid: {display: true, color: 'rgba(0,0,0,0.05)'}, beginAtZero: true}} 
                    }
                });
            }
            toggleLoading('kabkotaSection', false);
        }).catch(()=>{
            toggleLoading('caborSection', false);
            toggleLoading('kabkotaSection', false);
        });
    }

    function loadEvent() {
        toggleLoading('eventSection', true);
        fetch('/api/v1/dashboard/event-terkini' + buildQuery(getGlobalFilters())).then(r=>r.json()).then(data => {
            let html = '';
            let events = data.events || data;
            
            let titleEl = document.getElementById('eventSectionTitle');
            if (titleEl) {
                let titleText = data.is_kab_kota ? 'Semua Event Daerah' : 'Event Terkini';
                titleEl.innerHTML = `<i class="bi bi-calendar-event-fill text-info me-2"></i>${titleText}`;
            }

            if(!events || events.length === 0) html = '<div class="text-center text-muted py-5"><i class="bi bi-calendar-x fs-1 d-block text-black-50 mb-2"></i>Belum ada event terbaru</div>';
            else {
                html = events.map(e => {
                    let st = e.status === 'aktif' ? 'success' : (e.status === 'selesai' ? 'secondary' : 'danger');
                    let dt = e.tanggal_mulai ? new Date(e.tanggal_mulai).toLocaleDateString('id-ID', {day:'numeric', month:'short'}) : '-';
                    return `
                    <div class="list-group-item px-3 py-3 border-0 bg-light rounded-3 mb-2 shadow-sm transition-hover">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold mb-0 text-dark" style="line-height: 1.4;">${e.nama}</h6>
                            <span class="badge bg-${st} bg-opacity-10 text-${st} ms-2">${e.status}</span>
                        </div>
                        <div class="small text-muted d-flex flex-wrap gap-3">
                            <span><i class="bi bi-calendar text-primary"></i> ${dt}</span>
                            <span><i class="bi bi-tag-fill text-info"></i> ${e.jenis_event}</span>
                        </div>
                    </div>`;
                }).join('');
            }
            document.getElementById('eventList').innerHTML = html;
            toggleLoading('eventSection', false);
        }).catch(()=>toggleLoading('eventSection', false));
    }

    function reloadAll() {
        loadStats();
        if(document.getElementById('chartMedaliCabor')) loadPrestasi();
        loadSdm();
        loadCharts();
        loadEvent();
    }

    // Bind events
    ['filter_tahun_global', 'filter_kab_kota_global', 'filter_jenis_global'].forEach(id => {
        let el = document.getElementById(id);
        if(el) el.addEventListener('change', reloadAll);
    });
    
    ['filter_cabor_prestasi', 'filter_skala_prestasi', 'filter_difabel_prestasi'].forEach(id => {
        let el = document.getElementById(id);
        if(el) el.addEventListener('change', loadPrestasi);
    });
    
    let elPeran = document.getElementById('filter_peran_sdm');
    if(elPeran) elPeran.addEventListener('change', loadSdm);

    // Initial load
    reloadAll();
});
</script>
@endpush
