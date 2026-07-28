@extends('layouts.public-dark')

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
/* ── ORGANISASI STYLES ────────────────────────── */
.organisasi-section {
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
.filter-card select, .filter-card input {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border-radius: 8px;
}
.filter-card select:focus, .filter-card input:focus {
    background: rgba(255, 255, 255, 0.06);
    border-color: var(--tech-blue);
    box-shadow: 0 0 0 0.25rem rgba(0, 212, 255, 0.25);
    color: var(--text-primary);
}
.filter-card select option {
    background: var(--dark-bg);
    color: var(--text-primary);
}

.data-card {
    background: var(--dark-card);
    border-radius: 20px;
    border: 1px solid var(--glass-border);
    padding: 24px;
    backdrop-filter: blur(12px);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
}

/* DataTables Overrides for Dark Theme */
table.dataTable {
    color: var(--text-primary) !important;
    border-collapse: separate;
    border-spacing: 0 8px;
    background: transparent !important;
    --bs-table-bg: transparent;
    --bs-table-color: var(--text-primary);
}
table.dataTable thead th {
    border-bottom: 1px solid rgba(255,255,255,0.1);
    color: var(--tech-blue) !important;
    background: transparent !important;
    font-family: var(--font-tech);
    font-size: 0.8rem;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-weight: 600;
}
table.dataTable tbody tr {
    background: rgba(255,255,255,0.04) !important;
    box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05);
    transition: all 0.3s ease;
}
table.dataTable tbody tr:hover {
    background: rgba(0, 212, 255, 0.05) !important;
    box-shadow: inset 0 0 0 1px rgba(0, 212, 255, 0.2);
}
table.dataTable tbody td {
    border-top: 1px solid rgba(255,255,255,0.05);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    padding: 16px 12px;
    vertical-align: middle;
    background: transparent !important;
    color: var(--text-primary) !important;
}
table.dataTable tbody td:first-child {
    border-left: 1px solid rgba(255,255,255,0.05);
    border-top-left-radius: 12px;
    border-bottom-left-radius: 12px;
}
table.dataTable tbody td:last-child {
    border-right: 1px solid rgba(255,255,255,0.05);
    border-top-right-radius: 12px;
    border-bottom-right-radius: 12px;
}

.dataTables_wrapper .dataTables_info, 
.dataTables_wrapper .dataTables_length, 
.dataTables_wrapper .dataTables_filter {
    color: var(--text-secondary);
    font-size: 0.9rem;
    margin-bottom: 16px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    color: var(--text-primary) !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    border-radius: 8px !important;
    background: rgba(255,255,255,0.02) !important;
    margin: 0 4px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current, 
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: var(--tech-blue) !important;
    color: var(--dark-bg) !important;
    border-color: var(--tech-blue) !important;
}

.org-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid rgba(255,255,255,0.05);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.org-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; height: 100%;
    background: linear-gradient(180deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0) 100%);
    opacity: 0;
    transition: opacity 0.4s;
}
.org-card:hover {
    border-color: var(--tech-blue);
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.4), inset 0 0 0 1px rgba(0,212,255,0.2);
}
.org-card:hover::before { opacity: 1; }
.org-card:hover .org-title { color: var(--tech-blue) !important; }

.detail-link {
    cursor: pointer;
    color: var(--tech-blue);
    text-decoration: none;
    transition: all 0.3s ease;
}
.detail-link:hover {
    color: var(--emerald);
    text-shadow: 0 0 8px rgba(0, 255, 136, 0.4);
}

.org-logo {
    width: 48px;
    height: 48px;
    object-fit: contain;
    background: rgba(255,255,255,0.05);
    border-radius: 8px;
    padding: 4px;
    border: 1px solid rgba(255,255,255,0.1);
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
<section class="organisasi-section">
    <div class="section-container">
        
        <!-- HEADER -->
        <div class="row align-items-center mb-5 reveal">
            <div class="col-md-8">
                <div class="section-label">Pemangku Kepentingan</div>
                <h1 class="section-heading mb-0" style="font-size:2.5rem;">Data <span style="color:var(--victory-gold)">Organisasi</span></h1>
                <p class="section-desc mt-2 mb-0">Daftar Induk Organisasi Olahraga, Kepemudaan, dan Kepramukaan Provinsi Jawa Timur.</p>
            </div>
            <div class="col-md-4 mt-4 mt-md-0 text-md-end">
                <div class="d-inline-flex align-items-center gap-3 p-3 rounded-4" style="background:rgba(255,215,0,0.1); border:1px solid rgba(255,215,0,0.2);">
                    <div style="width:48px;height:48px;border-radius:12px;background:var(--victory-gold);display:flex;align-items:center;justify-content:center;color:var(--dark-bg);font-size:1.5rem;">
                        <i class="bi bi-diagram-3-fill"></i>
                    </div>
                    <div class="text-start">
                        <div class="font-tech fw-bold" style="font-size:1.5rem; color:var(--victory-gold); line-height:1;" id="countOrganisasi">—</div>
                        <div style="font-size:0.75rem; color:var(--text-secondary); text-transform:uppercase; letter-spacing:1px;">Organisasi Terdaftar</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER BAR -->
        <div class="filter-card reveal reveal-delay-1">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-tag me-1 text-tech-blue" style="color:var(--tech-blue)"></i> Jenis Domain
                    </label>
                    <select class="form-select form-select-sm" id="filterJenis">
                        <option value="">Semua Jenis</option>
                        @foreach($jenis as $j)
                        <option value="{{ $j->id }}">{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-bookmark me-1" style="color:var(--emerald)"></i> Skala
                    </label>
                    <select class="form-select form-select-sm" id="filterSkala">
                        <option value="">Semua Skala</option>
                        <option value="Provinsi">Provinsi</option>
                        <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold mb-2">
                        <i class="bi bi-search me-1" style="color:var(--victory-gold)"></i> Cari Organisasi
                    </label>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Ketik nama organisasi..." maxlength="100">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="nav-cta border-0 rounded-pill px-4" onclick="applyFilter()" style="width: 100%;">
                        <i class="bi bi-funnel-fill me-1"></i> Filter
                    </button>
                    <button class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="resetFilters()" style="border-color: rgba(255,255,255,0.2);">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- CARD GRID -->
        <div class="reveal reveal-delay-2">
            <div id="loadingIndicator" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-info mb-3" role="status"></div>
                <div class="text-secondary">Memuat data organisasi...</div>
            </div>
            
            <div class="row g-4" id="orgGrid"></div>
            
            <div class="d-flex justify-content-between align-items-center mt-5" id="orgPaginationContainer" style="display:none !important;">
                <div class="text-secondary small" id="orgPageInfo"></div>
                <div class="d-flex gap-2" id="orgPagination"></div>
            </div>
        </div>
        
    </div>
</section>

<!-- DETAIL MODAL -->
<div class="modal fade" id="orgModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="background: rgba(10, 15, 30, 0.98); backdrop-filter: blur(20px); border: 1px solid var(--glass-border);">
            <div class="modal-header border-bottom border-secondary" style="border-color:rgba(255,255,255,0.1) !important;">
                <h5 class="modal-title font-display fw-bold d-flex align-items-center gap-2" style="color:var(--text-primary)">
                    <div style="width:32px;height:32px;border-radius:8px;background:var(--victory-gold);display:flex;align-items:center;justify-content:center;color:var(--dark-bg);">
                        <i class="bi bi-diagram-3"></i>
                    </div>
                    Profil Organisasi
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" style="filter: invert(1) grayscale(100%) brightness(200%);"></button>
            </div>
            <div class="modal-body p-0" id="modalOrgBody">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border text-info mb-3" role="status"></div>
                    <p>Memuat profil...</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
let searchTimer;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', () => {
    // Pindahkan modal ke body untuk mencegah masalah z-index backdrop
    document.body.appendChild(document.getElementById('orgModal'));

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

    loadData();

    document.getElementById('filterJenis').addEventListener('change', () => { currentPage = 1; loadData(); });
    document.getElementById('filterSkala').addEventListener('change', () => { currentPage = 1; loadData(); });
    document.getElementById('filterSearch').addEventListener('input', function(){
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { currentPage = 1; loadData(); }, 400);
    });
});

async function loadData() {
    const jenis_id = document.getElementById('filterJenis').value;
    const skala = document.getElementById('filterSkala').value;
    const search = document.getElementById('filterSearch').value;
    
    let url = `/api/v1/public/organisasi?page=${currentPage}&per_page=12`;
    if (jenis_id) url += `&jenis_id=${jenis_id}`;
    if (skala) url += `&skala=${encodeURIComponent(skala)}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    document.getElementById('loadingIndicator').style.display = 'block';
    document.getElementById('orgGrid').innerHTML = '';
    document.getElementById('orgPaginationContainer').style.setProperty('display', 'none', 'important');

    try {
        const r = await fetch(url);
        const res = await r.json();
        
        if (res.total !== undefined) {
            document.getElementById('countOrganisasi').textContent = res.total.toLocaleString('id-ID');
        }

        renderCards(res.data || res);
        renderPagination(res);
    } catch(e) {
        document.getElementById('orgGrid').innerHTML = `<div class="col-12 text-center text-danger py-5"><i class="bi bi-exclamation-triangle fs-1"></i><div class="mt-3">Gagal memuat data</div></div>`;
    } finally {
        document.getElementById('loadingIndicator').style.display = 'none';
    }
}

function renderCards(data) {
    const grid = document.getElementById('orgGrid');
    if (!data.length) {
        grid.innerHTML = `
        <div class="col-12 text-center py-5">
            <i class="bi bi-inbox text-secondary" style="font-size:4rem; opacity:0.5;"></i>
            <h5 class="text-secondary mt-3">Tidak ada data organisasi</h5>
        </div>`;
        return;
    }

    grid.innerHTML = data.map(r => `
        <div class="col-lg-4 col-md-6">
            <div class="org-card h-100 p-4 rounded-4" onclick="showDetail(${r.id})">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="${r.logo ? '/storage/'+r.logo : '/logo/4_sportif.png'}" class="rounded-3" style="width:60px;height:60px;object-fit:contain;background:rgba(255,255,255,0.05);padding:5px; border:1px solid rgba(255,255,255,0.1);" onerror="this.src='/logo/4_sportif.png'">
                    <div>
                        <h5 class="org-title fw-bold text-white mb-1" style="font-family:var(--font-display);font-size:1.1rem;line-height:1.2; transition:color 0.3s;">${r.nama}</h5>
                        ${r.skala ? `<span class="badge" style="background:rgba(0,255,136,0.1);color:var(--emerald);border:1px solid rgba(0,255,136,0.2);">${r.skala.nama || r.skala}</span>` : ''}
                    </div>
                </div>
                <div class="small text-secondary mb-4" style="line-height:1.5;">
                    <i class="bi bi-geo-alt-fill me-1" style="color:var(--tech-blue-dim)"></i>${r.alamat || 'Alamat tidak tersedia'}
                </div>
                <div class="d-flex justify-content-between align-items-center pt-3 mt-auto border-top" style="border-color:rgba(255,255,255,0.1)!important">
                    ${r.jenis ? `<span class="badge" style="background:rgba(0,212,255,0.1);color:var(--tech-blue);border:1px solid rgba(0,212,255,0.2);"><i class="bi bi-tag-fill me-1"></i>${r.jenis.nama}</span>` : '<span></span>'}
                    ${r.sk_aktif ? '<span class="text-emerald fw-semibold" style="font-size:0.8rem;"><i class="bi bi-check-circle-fill me-1"></i>SK Aktif</span>' : '<span class="text-danger fw-semibold" style="font-size:0.8rem;"><i class="bi bi-x-circle-fill me-1"></i>Tidak Aktif</span>'}
                </div>
            </div>
        </div>
    `).join('');
}

function renderPagination(res) {
    if (!res.last_page || res.last_page <= 1) return;
    
    const container = document.getElementById('orgPaginationContainer');
    container.style.setProperty('display', 'flex', 'important');
    
    document.getElementById('orgPageInfo').innerHTML = `Menampilkan <strong class="text-white">${res.from || 0}</strong> - <strong class="text-white">${res.to || 0}</strong> dari <strong class="text-white">${res.total}</strong> organisasi`;
    
    let html = '';
    
    // Prev
    html += `<button class="btn btn-sm btn-outline-light ${res.current_page === 1 ? 'disabled' : ''}" style="border-color:rgba(255,255,255,0.1);" onclick="changePage(${res.current_page - 1})"><i class="bi bi-chevron-left"></i></button>`;
    
    // Pages
    for (let i = 1; i <= res.last_page; i++) {
        if (i === 1 || i === res.last_page || (i >= res.current_page - 1 && i <= res.current_page + 1)) {
            html += `<button class="btn btn-sm ${i === res.current_page ? 'btn-primary' : 'btn-outline-light'}" style="${i === res.current_page ? 'background:var(--tech-blue);border:none;color:var(--dark-bg);font-weight:bold;' : 'border-color:rgba(255,255,255,0.1);'}" onclick="changePage(${i})">${i}</button>`;
        } else if (i === res.current_page - 2 || i === res.current_page + 2) {
            html += `<button class="btn btn-sm btn-outline-light disabled" style="border-color:rgba(255,255,255,0.1);">...</button>`;
        }
    }
    
    // Next
    html += `<button class="btn btn-sm btn-outline-light ${res.current_page === res.last_page ? 'disabled' : ''}" style="border-color:rgba(255,255,255,0.1);" onclick="changePage(${res.current_page + 1})"><i class="bi bi-chevron-right"></i></button>`;
    
    document.getElementById('orgPagination').innerHTML = html;
}

function changePage(page) {
    currentPage = page;
    loadData();
    document.querySelector('.organisasi-section').scrollIntoView({behavior: 'smooth', block: 'start'});
}

function applyFilter() {
    currentPage = 1;
    loadData();
}

function resetFilters() {
    document.getElementById('filterJenis').value='';
    document.getElementById('filterSkala').value='';
    document.getElementById('filterSearch').value='';
    currentPage = 1;
    loadData();
}

async function showDetail(id) {
    let modalEl = document.getElementById('orgModal');
    let modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    document.getElementById('modalOrgBody').innerHTML = `
        <div class="text-center py-5 text-muted">
            <div class="spinner-border text-info mb-3" role="status"></div>
            <p>Memuat profil organisasi...</p>
        </div>`;
    modal.show();

    try {
        const r = await fetch(`/api/v1/public/organisasi/${id}`);
        if (!r.ok) throw new Error();
        const org = await r.json();
        
        // Find latest pengurus
        let latestPengurus = null;
        if (org.pengurus && org.pengurus.length > 0) {
            // Sort by tgl_awal descending or just take the last one
            latestPengurus = org.pengurus.sort((a,b) => new Date(b.tgl_awal) - new Date(a.tgl_awal))[0];
        }
        
        const ketuaName = latestPengurus && latestPengurus.ketua ? latestPengurus.ketua.nama : '—';
        const sekjenName = latestPengurus && latestPengurus.sekretaris ? latestPengurus.sekretaris.nama : '—';
        const skNumber = latestPengurus && latestPengurus.sk_kepengurusan ? latestPengurus.sk_kepengurusan : (org.sk_pendirian || '—');

        document.getElementById('modalOrgBody').innerHTML = `
        <div class="p-4">
            <div class="text-center mb-4 pb-4 border-bottom" style="border-color:rgba(255,255,255,0.1) !important;">
                <img src="${org.logo ? '/storage/'+org.logo : '/logo/4_sportif.png'}" style="width:100px; height:100px; object-fit:contain; background:rgba(255,255,255,0.05); padding:10px; border-radius:16px; margin-bottom:16px; border:1px solid rgba(255,255,255,0.1);" onerror="this.src='/logo/4_sportif.png'">
                <h4 class="font-display fw-bold text-white mb-2">${org.nama}</h4>
                <div class="d-flex justify-content-center gap-2">
                    ${org.jenis ? `<span class="badge" style="background:rgba(0,212,255,0.1);color:var(--tech-blue);border:1px solid rgba(0,212,255,0.2);">${org.jenis.nama}</span>` : ''}
                    ${org.skala ? `<span class="badge" style="background:rgba(0,255,136,0.1);color:var(--emerald);border:1px solid rgba(0,255,136,0.2);">${org.skala.nama || org.skala}</span>` : ''}
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <div class="p-3 h-100 rounded-4" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05);">
                        <h6 class="text-secondary small fw-bold mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Kepengurusan (Terbaru)</h6>
                        <div class="mb-2">
                            <div class="small" style="color: #94a3b8;">Ketua Umum</div>
                            <div class="fw-semibold text-white">${ketuaName}</div>
                        </div>
                        <div class="mb-2">
                            <div class="small" style="color: #94a3b8;">Sekretaris Jenderal</div>
                            <div class="fw-semibold text-white">${sekjenName}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-3 h-100 rounded-4" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05);">
                        <h6 class="text-secondary small fw-bold mb-3"><i class="bi bi-envelope me-2 text-success"></i>Kontak & Alamat</h6>
                        <div class="mb-2">
                            <div class="small" style="color: #94a3b8;">Email</div>
                            <div class="fw-semibold text-white">${org.email||'—'}</div>
                        </div>
                        <div class="mb-2">
                            <div class="small" style="color: #94a3b8;">Telepon</div>
                            <div class="fw-semibold text-white">${org.telepon||'—'}</div>
                        </div>
                        <div class="mb-2">
                            <div class="small" style="color: #94a3b8;">Alamat Sekretariat</div>
                            <div class="small text-white">${org.alamat||'—'}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-3 rounded-4" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.05);">
                <h6 class="text-secondary small fw-bold mb-3"><i class="bi bi-file-earmark-text me-2 text-warning"></i>Legalitas SK</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="small mb-1" style="color: #94a3b8;">Nomor SK</div>
                        <div class="fw-bold text-white" style="font-size:0.9rem">${skNumber}</div>
                    </div>
                    <div class="col-6 border-start border-secondary" style="border-color:rgba(255,255,255,0.1) !important;">
                        <div class="small mb-1" style="color: #94a3b8;">Status SK</div>
                        ${org.sk_aktif 
                            ? '<span class="badge" style="background:rgba(0,255,136,0.1);color:var(--emerald);border:1px solid rgba(0,255,136,0.2);"><i class="bi bi-check-circle me-1"></i>Aktif</span>' 
                            : '<span class="badge" style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);"><i class="bi bi-x-circle me-1"></i>Tidak Aktif</span>' 
                        }
                    </div>
                </div>
            </div>
        </div>`;
    } catch(e) {
        document.getElementById('modalOrgBody').innerHTML = `
            <div class="text-center text-danger p-5">
                <i class="bi bi-exclamation-triangle d-block mb-3" style="font-size:3rem"></i>
                <h5>Gagal Memuat Profil</h5>
            </div>`;
    }
}
</script>
@endpush
