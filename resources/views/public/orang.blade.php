@extends('layouts.public-dark')

@push('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
/* ── ORANG (DATA SDM) STYLES ────────────────────────── */
.orang-section {
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
.filter-card select:disabled {
    background: rgba(255,255,255,0.01);
    color: rgba(255,255,255,0.3);
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

/* Offcanvas Dark */
.offcanvas {
    background: rgba(10, 15, 30, 0.95);
    backdrop-filter: blur(20px);
    border-left: 1px solid var(--glass-border);
    color: var(--text-primary);
}
.offcanvas-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}
.btn-close {
    filter: invert(1) grayscale(100%) brightness(200%);
}
.offcanvas-body {
    scrollbar-color: var(--tech-blue-dim) transparent;
}
.bg-light-dark {
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.05);
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
<section class="orang-section">
    <div class="section-container">
        
        <!-- HEADER -->
        <div class="row align-items-center mb-5 reveal">
            <div class="col-md-5">
                <div class="section-label">Data Sumber Daya Manusia</div>
                <h1 class="section-heading mb-0" style="font-size:2.5rem;">Insan <span style="color:var(--tech-blue)">Olahraga</span></h1>
                <p class="section-desc mt-2 mb-0">Database terpadu Atlet, Pelatih, Wasit/Juri &amp; Insan Olahraga Jawa Timur.</p>
            </div>
            <div class="col-md-7 mt-4 mt-md-0">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="stat-card" style="padding: 20px 10px;">
                            <div class="stat-number" id="summaryTotal" style="font-size: 1.8rem;">—</div>
                            <div class="stat-label" style="font-size: 0.75rem;">Total Orang</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card" style="padding: 20px 10px;">
                            <div class="stat-number" id="summaryAtlet" style="font-size: 1.8rem; color:var(--emerald)">—</div>
                            <div class="stat-label" style="font-size: 0.75rem;">Atlet</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card" style="padding: 20px 10px;">
                            <div class="stat-number" id="summaryPelatih" style="font-size: 1.8rem; color:var(--victory-gold)">—</div>
                            <div class="stat-label" style="font-size: 0.75rem;">Pelatih</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="stat-card" style="padding: 20px 10px;">
                            <div class="stat-number" id="summaryWasit" style="font-size: 1.8rem; color:var(--neon-purple)">—</div>
                            <div class="stat-label" style="font-size: 0.75rem;">Wasit/Juri</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILTER -->
        <div class="filter-card reveal reveal-delay-1">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-tag me-1"></i> Jenis Domain</label>
                    <select class="form-select form-select-sm" id="filterJenis">
                        <option value="">Semua Jenis</option>
                        @foreach($jenis as $j)
                            <option value="{{ $j->id }}">{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-person-badge me-1" style="color:var(--emerald)"></i> Peran</label>
                    <select class="form-select form-select-sm" id="filterPeran" disabled>
                        <option value="">Pilih Domain Dulu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-dribbble me-1" style="color:var(--victory-gold)"></i> Cabang Olahraga</label>
                    <select class="form-select form-select-sm" id="filterCabor" disabled>
                        <option value="">Pilih Domain Dulu</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-geo-alt me-1" style="color:var(--neon-purple)"></i> Domisili</label>
                    <select class="form-select form-select-sm" id="filterDomisili">
                        <option value="">Semua Kab/Kota</option>
                        @foreach($kabKota as $k)
                            <option value="{{ $k->id }}">{{ $k->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="bi bi-gender-ambiguous me-1" style="color:var(--tech-blue)"></i> Gender</label>
                    <select class="form-select form-select-sm" id="filterGender">
                        <option value="">Semua</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="bi bi-search me-1" style="color:var(--emerald)"></i> Cari Nama</label>
                    <input type="text" class="form-control form-control-sm" id="filterSearch" placeholder="Ketik nama...">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-sm btn-primary w-100 fw-bold" onclick="applyFilter()" style="background:var(--emerald); border:none; color:var(--dark-bg)"><i class="bi bi-funnel-fill me-1"></i> Filter</button>
                    <button class="btn btn-sm btn-outline-light" onclick="resetFilters()" title="Reset Filter"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
            </div>
        </div>

        <!-- TABLE -->
        <div class="data-card reveal reveal-delay-2">
            <div class="table-responsive">
                <table class="table table-borderless table-dark align-middle w-100" id="orangTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama & NIK</th>
                            <th>Gender</th>
                            <th>Domisili</th>
                            <th>Peran / Status</th>
                            <th>Cabang Olahraga</th>
                            <th>Prestasi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<!-- OFFCANVAS PROFIL -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="detailOffcanvas" aria-labelledby="detailOffcanvasLabel" style="width: 450px; border-left:1px solid rgba(255,255,255,0.1)">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-bold" id="detailOffcanvasLabel" style="font-family:var(--font-display); color:var(--tech-blue)"><i class="bi bi-person-vcard me-2"></i>Detail Profil</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0" id="detailBody">
        <div class="d-flex align-items-center justify-content-center h-100 text-muted">
            <div class="text-center">
                <div class="spinner-border text-info mb-3" role="status"></div>
                <p>Memuat identitas digital...</p>
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
let orangTable;
const allCaborOptions = @json($cabors);

document.addEventListener('DOMContentLoaded', async () => {
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

    // Load summary stats
    fetch('/api/v1/public/orang/summary').then(r=>r.json()).then(d=>{
        if(d.total !== undefined) document.getElementById('summaryTotal').textContent = d.total.toLocaleString('id-ID');
        if(d.atlet !== undefined) document.getElementById('summaryAtlet').textContent = d.atlet.toLocaleString('id-ID');
        if(d.pelatih !== undefined) document.getElementById('summaryPelatih').textContent = d.pelatih.toLocaleString('id-ID');
        if(d.wasit !== undefined) document.getElementById('summaryWasit').textContent = d.wasit.toLocaleString('id-ID');
    }).catch(()=>{});

    let searchTimer;
    // Init DataTable
    orangTable = $('#orangTable').DataTable({
        serverSide: true,
        processing: true,
        pageLength: 15,
        order: [[1,'asc']],
        ajax: function (data, callback, settings) {
            const page = Math.ceil(data.start / data.length) + 1;
            const per_page = data.length === -1 ? 100 : data.length;
            
            let url = `/api/v1/public/orang?page=${page}&per_page=${per_page}`;
            url += `&jenis_id=${document.getElementById('filterJenis').value}`;
            url += `&peran_id=${document.getElementById('filterPeran').value}`;
            url += `&cabor_id=${document.getElementById('filterCabor').value}`;
            url += `&domisili_id=${document.getElementById('filterDomisili').value}`;
            url += `&gender=${document.getElementById('filterGender').value}`;
            url += `&search=${encodeURIComponent(document.getElementById('filterSearch').value)}`;
            
            fetch(url)
                .then(r => r.json())
                .then(res => {
                    if (res.total !== undefined) {
                        document.getElementById('summaryTotal').textContent = res.total.toLocaleString('id-ID');
                    }
                    callback({
                        draw: data.draw,
                        recordsTotal: res.total || 0,
                        recordsFiltered: res.total || 0,
                        data: res.data || []
                    });
                });
        },
        columns: [
            { data:null, orderable:false, render:(d,t,r,m) => `<span class="text-secondary">${m.row + m.settings._iDisplayStart + 1}</span>`, width:'40px' },
            { data:'nama', render:(v,t,r) => `
                <a class="detail-link fw-bold" onclick="showDetail(${r.id})" style="font-family:var(--font-display); font-size:1.05rem">${v}</a>
                <div class="text-secondary mt-1" style="font-size:0.8rem; font-family:var(--font-tech)">${r.nik||'NIK Tidak Terdaftar'}</div>
            ` },
            { data:'gender', render: v => v==='L'
                ? '<span class="badge" style="background:rgba(0,212,255,0.1);color:var(--tech-blue);border:1px solid rgba(0,212,255,0.2);">Laki-laki</span>'
                : v==='P' ? '<span class="badge" style="background:rgba(168,85,247,0.1);color:var(--neon-purple);border:1px solid rgba(168,85,247,0.2);">Perempuan</span>' : '-' },
            { data:'domisili.name', render: v => v ? `<span style="color:var(--text-secondary)"><i class="bi bi-geo-alt-fill me-1" style="color:var(--tech-blue-dim)"></i>${v}</span>` : '-' },
            { data:'status_list', defaultContent:'-', render: (v,t,r) => {
                if (!v?.length) return '-';
                return v.slice(0,3).map(s=>`<span class="badge" style="background:rgba(0,255,136,0.1);color:var(--emerald);border:1px solid rgba(0,255,136,0.2); margin-right:4px;">${s.peran?.nama||'-'}</span>`).join('') + (v.length>3?`<small class="text-muted ms-1">+${v.length-3}</small>`:'');
            }},
            { data:'status_list', defaultContent:'-', render: v => {
                if (!v?.length) return '-';
                const unique = [...new Set(v.filter(s=>s.cabor).map(s=>s.cabor.nama))];
                return unique.slice(0,2).map(n=>`<span class="badge" style="background:rgba(255,215,0,0.1);color:var(--victory-gold);border:1px solid rgba(255,215,0,0.2); margin-right:4px;">${n}</span>`).join('') + (unique.length>2?`<small class="text-muted ms-1">+${unique.length-2}</small>`:'' );
            }},
            { data:'riwayat_count', defaultContent:'0', render: v => v
                ? `<span class="badge" style="background:linear-gradient(135deg, var(--tech-blue), var(--emerald)); color:var(--dark-bg); font-weight:700;"><i class="bi bi-trophy-fill me-1"></i>${v} Prestasi</span>`
                : '<span class="text-secondary">—</span>' },
        ],
        language: {
            processing: '<div class="spinner-border text-info" role="status"></div><br><span class="mt-2 d-inline-block">Memproses Data...</span>',
            lengthMenu: 'Tampilkan <select class="form-select form-select-sm d-inline-block w-auto mx-1" style="background:rgba(255,255,255,0.05); color:var(--text-primary); border-color:rgba(255,255,255,0.1);"><option value="15">15</option><option value="50">50</option><option value="-1">Semua</option></select>',
            info: '<span class="text-secondary">Menampilkan _START_ sampai _END_ dari <strong style="color:var(--text-primary)">_TOTAL_</strong> baris</span>',
            emptyTable: 'Tidak ada data ditemukan dalam sistem.',
            zeroRecords: 'Data yang dicari tidak ditemukan.',
            paginate: { first:'«', last:'»', next:'›', previous:'‹' },
            search: ''
        },
        dom: '<"d-flex justify-content-between align-items-center mb-3"l>rt<"d-flex justify-content-between align-items-center mt-4"ip>',
    });

    document.getElementById('filterJenis').addEventListener('change', function(){
        const jenisId = this.value;
        loadPeranByJenis(jenisId);
        loadCaborByJenis(jenisId);
        applyFilter();
    });
    ['filterPeran','filterCabor','filterDomisili','filterGender'].forEach(id=>{
        document.getElementById(id).addEventListener('change', applyFilter);
    });
    document.getElementById('filterSearch').addEventListener('input', function(){
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilter, 400);
    });
});

function buildParams(d) {
    d.jenis_id   = document.getElementById('filterJenis').value;
    d.peran_id   = document.getElementById('filterPeran').value;
    d.cabor_id   = document.getElementById('filterCabor').value;
    d.domisili_id= document.getElementById('filterDomisili').value;
    d.gender     = document.getElementById('filterGender').value;
    d.search     = document.getElementById('filterSearch').value;
    return d;
}

async function loadPeranByJenis(jenisId) {
    const sel = document.getElementById('filterPeran');
    if (!jenisId) {
        sel.innerHTML = '<option value="">Pilih Domain Dulu</option>';
        sel.disabled = true;
        return;
    }
    sel.disabled = false;
    sel.innerHTML = '<option value="">Semua Peran</option>';
    try {
        const r = await fetch(`/api/v1/public/peran?jenis_id=${jenisId}&all`);
        const d = await r.json();
        (d.data||d).forEach(p => {
            sel.insertAdjacentHTML('beforeend', `<option value="${p.id}">${p.nama}</option>`);
        });
    } catch(e){}
}

function loadCaborByJenis(jenisId) {
    const sel = document.getElementById('filterCabor');
    if (!jenisId) {
        sel.innerHTML = '<option value="">Pilih Domain Dulu</option>';
        sel.disabled = true;
        return;
    }
    sel.disabled = false;
    sel.innerHTML = '<option value="">Semua Cabor</option>';
    const id = parseInt(jenisId);
    const jenisToTipe = {1:'olahraga_prestasi', 2:'olahraga_masyarakat'};

    if (id === 3 || id === 4) {
        sel.innerHTML = '<option value="">Tidak tersedia</option>';
        sel.disabled = true;
        return;
    }
    const tipe = jenisToTipe[id];
    if (tipe) {
        allCaborOptions.filter(c => c.tipe === tipe).forEach(c => {
            sel.insertAdjacentHTML('beforeend', `<option value="${c.id}" data-tipe="${c.tipe}">${c.nama}</option>`);
        });
    } else {
        allCaborOptions.forEach(c => {
            sel.insertAdjacentHTML('beforeend', `<option value="${c.id}" data-tipe="${c.tipe}">${c.nama}</option>`);
        });
    }
}

function applyFilter() {
    if (orangTable) orangTable.ajax.reload();
}

function resetFilters() {
    ['filterJenis','filterPeran','filterCabor','filterDomisili','filterGender'].forEach(id=>{
        document.getElementById(id).value='';
    });
    document.getElementById('filterCabor').disabled = false;
    loadCaborByJenis('');
    document.getElementById('filterSearch').value='';
    if (orangTable) orangTable.ajax.reload();
}

async function showDetail(id) {
    const oc = new bootstrap.Offcanvas(document.getElementById('detailOffcanvas'));
    document.getElementById('detailBody').innerHTML = `
        <div class="d-flex align-items-center justify-content-center" style="height:300px">
            <div class="text-center text-muted">
                <div class="spinner-border text-info mb-3" role="status"></div>
                <p>Memuat identitas digital...</p>
            </div>
        </div>`;
    oc.show();

    try {
        const r = await fetch(`/api/v1/public/orang/${id}`);
        if (!r.ok) throw new Error('HTTP ' + r.status);
        const d = await r.json();
        const statuses = d.status_list || [];
        const riwayat  = d.riwayat_event || [];

        document.getElementById('detailBody').innerHTML = `
        <div class="p-4">
            <div class="text-center mb-4">
                <div style="width:80px;height:80px;border-radius:24px;background:linear-gradient(135deg, var(--tech-blue), var(--emerald));color:var(--dark-bg);font-size:2.5rem;font-weight:700;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; box-shadow: 0 10px 20px rgba(0,255,136,0.2);">
                    ${d.nama?.charAt(0)?.toUpperCase()||'?'}
                </div>
                <h4 class="fw-bold mb-1" style="font-family:var(--font-display); color:var(--text-primary)">${d.nama||'-'}</h4>
                <div style="font-family:var(--font-tech); font-size:0.85rem; letter-spacing:1px; color:var(--tech-blue)">ID: ${d.nik||'TIDAK TERDAFTAR'}</div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <div class="p-3 bg-light-dark rounded-4 text-center h-100">
                        <div class="small text-secondary mb-1">Gender</div>
                        <div class="fw-bold" style="color:var(--text-primary)">${d.gender==='L'?'Laki-laki':d.gender==='P'?'Perempuan':'—'}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light-dark rounded-4 text-center h-100">
                        <div class="small text-secondary mb-1">Domisili</div>
                        <div class="fw-bold" style="font-size:.9rem; color:var(--text-primary)">${d.domisili?.name||'—'}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light-dark rounded-4 text-center h-100">
                        <div class="small text-secondary mb-1">Umur</div>
                        <div class="fw-bold" style="color:var(--text-primary)">${d.umur ? d.umur + ' thn' : '—'}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light-dark rounded-4 text-center h-100">
                        <div class="small text-secondary mb-1">Gol. Darah</div>
                        <div class="fw-bold" style="color:var(--text-primary)">${d.gol_darah||'—'}</div>
                    </div>
                </div>
            </div>

            ${statuses.length ? `
            <div class="section-label mb-3" style="font-size:0.65rem;">Peran & Status Domain</div>
            ${statuses.map(s=>`
            <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light-dark rounded-4">
                <div>
                    <span class="badge" style="background:rgba(0,255,136,0.1);color:var(--emerald);border:1px solid rgba(0,255,136,0.2); margin-right:6px;">${s.peran?.nama||'—'}</span>
                    ${s.cabor ? `<span class="badge" style="background:rgba(255,215,0,0.1);color:var(--victory-gold);border:1px solid rgba(255,215,0,0.2);">${s.cabor.nama}</span>` : ''}
                </div>
                <small class="text-secondary" style="font-size:0.75rem">${s.jenis?.nama||''}</small>
            </div>`).join('')}` : ''}

            ${riwayat.length ? `
            <div class="section-label mb-3 mt-4" style="font-size:0.65rem;">Riwayat Prestasi</div>
            ${riwayat.map(rv=>`
            <div class="mb-3 p-3 bg-light-dark rounded-4 position-relative overflow-hidden">
                <div style="position:absolute; top:0; left:0; width:3px; height:100%; background:var(--victory-gold);"></div>
                <div class="fw-bold" style="font-size:0.95rem; color:var(--text-primary)">${rv.event?.nama||'—'}</div>
                <div class="d-flex align-items-center gap-2 mt-2">
                    ${rv.medali&&rv.medali!=='-'?`<span class="badge" style="background:rgba(255,215,0,0.15);color:var(--victory-gold);border:1px solid rgba(255,215,0,0.3);"><i class="bi bi-award-fill me-1"></i>${rv.medali.toUpperCase()}</span>`:''}
                    <span class="small text-secondary">${rv.prestasi||''} ${rv.cabor?'· '+rv.cabor.nama:''}</span>
                </div>
            </div>`).join('')}` : ''}
        </div>`;
    } catch(e) {
        document.getElementById('detailBody').innerHTML = `
            <div class="text-center text-danger p-5">
                <i class="bi bi-exclamation-triangle d-block mb-3" style="font-size:3rem"></i>
                <h5>Koneksi Terputus</h5>
                <p class="small text-secondary">Gagal menghubungi server data.</p>
            </div>`;
    }
}
</script>
@endpush
