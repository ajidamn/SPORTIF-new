@extends('layouts.admin')
@section('title', $pageTitle)

@section('content')
<div class="admin-card">
    <div class="card-header">
        <span><i class="bi bi-database me-2 text-primary"></i>{{ $pageTitle }}</span>
        <div class="crud-toolbar d-flex gap-2">
            {{-- Import & Export (conditional via JS) --}}
            <button class="btn-toolbar btn btn-outline-success btn-sm" id="btnImport" style="display:none" onclick="openImportModal()">
                <i class="bi bi-file-earmark-arrow-up me-1"></i><span class="btn-text">Import</span>
            </button>
            <button class="btn-toolbar btn btn-outline-info btn-sm" id="btnExport" style="display:none" onclick="openExportModal()">
                <i class="bi bi-file-earmark-arrow-down me-1"></i><span class="btn-text">Export</span>
            </button>
            @if($pageSlug !== 'log-sistem')
            <button class="btn-toolbar btn-admin-primary" id="btnTambah" onclick="openCreateModal()">
                <i class="bi bi-plus-lg"></i> <span class="btn-text">Tambah Data</span>
            </button>
            @endif
            <button class="btn-toolbar btn btn-outline-secondary" onclick="reloadTable()">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="px-3 pt-3 pb-1" id="filterBar" style="display:none">
        <div class="d-flex align-items-center mb-2">
            <i class="bi bi-funnel me-2 text-primary"></i>
            <span class="fw-semibold small text-muted">FILTER DATA</span>
            <button class="btn btn-sm btn-link text-danger ms-auto p-0" onclick="resetFilters()" title="Reset Filter">
                <i class="bi bi-x-circle me-1"></i>Reset
            </button>
        </div>
        <div class="row g-2 mb-2" id="filterFields"></div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="crudTable" style="width:100%">
                <thead></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL UTAMA — Tambah/Edit (multi-tab untuk Orang, Prasarana, Event)
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="formModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" id="formModalDialog" style="max-width:860px">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white py-3">
                <h5 class="modal-title" id="formModalTitle">
                    <i class="bi bi-pencil-square me-2"></i>Form Data
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            {{-- Tab Nav (tampil hanya untuk halaman multi-tab) --}}
            <ul class="nav nav-tabs px-3 pt-2 border-bottom-0 bg-light" id="modalTabs" style="display:none!important"></ul>

            <form id="crudForm" enctype="multipart/form-data">
                <div class="modal-body py-3" id="formFields" style="max-height:70vh;overflow-y:auto;"></div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-admin-primary px-4" id="btnSave">
                        <i class="bi bi-check-lg me-1"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL IMPORT — Guideline, Template, Upload, Progress, Results
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:680px">
        <div class="modal-content">
            <div class="modal-header bg-success bg-gradient text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-up me-2"></i>Import Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Step 1: Guidelines --}}
                <div id="importStep1">
                    <div class="alert alert-info py-2 mb-3">
                        <strong><i class="bi bi-info-circle me-1"></i>Panduan Import</strong>
                        <ol class="mb-1 mt-2 ps-3 small">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai kolom yang tersedia (header jangan diubah)</li>
                            <li>Untuk kolom relasi (Jenis, Kab/Kota, dll), isi dengan <strong>nama</strong> persis sesuai data master</li>
                            <li>Klik tombol referensi di bawah untuk melihat data master</li>
                            <li>Format tanggal: <code>YYYY-MM-DD</code> atau <code>DD/MM/YYYY</code></li>
                            <li>Maksimal 5.000 baris per file. Data >500 baris diproses bertahap</li>
                        </ol>
                    </div>

                    <div class="d-flex gap-2 mb-3">
                        <button class="btn btn-success btn-sm" onclick="downloadTemplate()">
                            <i class="bi bi-download me-1"></i>Download Template
                        </button>
                    </div>

                    {{-- Reference Data Buttons (dynamic via JS) --}}
                    <div id="importRefButtons" class="mb-3" style="display:none">
                        <label class="form-label fw-semibold small text-muted">
                            <i class="bi bi-book me-1"></i>Referensi Data Master
                        </label>
                        <div class="d-flex flex-wrap gap-2" id="importRefBtnGroup"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="importFile" accept=".xlsx,.xls,.csv">
                        <div class="form-text">Format: .xlsx, .xls, .csv — Maks. 10MB</div>
                    </div>
                </div>

                {{-- Step 2: Progress --}}
                <div id="importStep2" style="display:none">
                    <div class="text-center py-3">
                        <div class="spinner-border text-success mb-3" role="status"></div>
                        <h6 id="importProgressText">Memproses data...</h6>
                        <div class="progress mt-3" style="height:8px">
                            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" id="importProgressBar" style="width:0%"></div>
                        </div>
                    </div>
                </div>

                {{-- Step 3: Results --}}
                <div id="importStep3" style="display:none">
                    <div id="importResultSummary"></div>
                    <div id="importResultDetail" style="max-height:300px;overflow-y:auto;"></div>
                </div>
            </div>
            <div class="modal-footer" id="importFooter">
                <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-success" id="btnDoImport" onclick="doImport()">
                    <i class="bi bi-upload me-1"></i>Upload & Proses
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL EXPORT — Filter selection
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered" style="max-width:520px">
        <div class="modal-content">
            <div class="modal-header bg-info bg-gradient text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-arrow-down me-2"></i>Export Data</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Pilih filter untuk menentukan data yang akan di-export. Kosongkan semua untuk export seluruh data.</p>
                <div id="exportFilterFields" class="row g-2"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <button class="btn btn-info text-white" onclick="doExport()">
                    <i class="bi bi-download me-1"></i>Download Excel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODAL REFERENSI DATA MASTER
═══════════════════════════════════════════════════════ --}}
<div class="modal fade" id="refDataModal" tabindex="-1" style="z-index:1070">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title" id="refDataTitle"><i class="bi bi-book me-2"></i>Referensi</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm table-striped mb-0" id="refDataTable">
                    <thead class="table-dark"><tr><th style="width:60px">ID</th><th>Nama</th></tr></thead>
                    <tbody id="refDataBody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Toast container --}}
<div id="toastBox" style="position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;min-width:280px"></div>
@endsection

@push('scripts')
{{-- DataTables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<style>
.tab-content-section { display:none; }
.tab-content-section.active { display:block; }
.status-row { background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:10px 12px; margin-bottom:8px; position:relative; }
.status-row .remove-row { position:absolute; top:8px; right:8px; background:none; border:none; color:#ef4444; cursor:pointer; font-size:1.1rem; }
.foto-preview { width:80px; height:80px; object-fit:cover; border-radius:6px; border:1px solid #e2e8f0; }
.autocomplete-results { position:absolute; z-index:1060; background:#fff; border:1px solid #e2e8f0; border-radius:8px; max-height:220px; overflow-y:auto; width:100%; box-shadow:0 4px 12px rgba(0,0,0,.1); }
.autocomplete-item { padding:8px 12px; cursor:pointer; font-size:.875rem; border-bottom:1px solid #f1f5f9; }
.autocomplete-item:hover { background:#f1f9ff; }
.riwayat-row { background:#fffbf0; border:1px solid #fde68a; border-radius:8px; padding:10px 12px; margin-bottom:8px; position:relative; }
.medali-emas { background:#fbbf24; color:#000; }
.medali-perak { background:#94a3b8; color:#fff; }
.medali-perunggu { background:#b45309; color:#fff; }
</style>

<script>
const pageSlug   = '{{ $pageSlug }}';
const isReadOnly = {{ ($isReadOnly ?? false) ? 'true' : 'false' }};
const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.content;
const userKabKotaId = {{ auth()->user()->kab_kota_id ?? 'null' }};
let dataTable    = null;
let editingId    = null;
let _isViewMode  = false;
let _prasaranaCache = null;

// ═══ KONFIGURASI HALAMAN ══════════════════════════════════
const PAGES = {

    // ── DATA ORANG ───────────────────────────────────────
    'orang': {
        api: '/api/v1/orang',
        multiTab: true,
        modalSize: '900px',
        importable: true,
        exportable: true,
        refData: [
            {label:'Kab/Kota',cache:'_kabKotaCache',nameField:'name'},
            {label:'Cabor',cache:'_caborCache',nameField:'nama'},
            {label:'Jenis',cache:'_jenisCache',nameField:'nama'},
            {label:'Peran',cache:'_peranCache',nameField:'nama'},
            {label:'Skala',cache:'_skalaCache',nameField:'nama'},
        ],
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Nama', data:'nama', render:(v,t,r)=>{ const idHtml = r.sportif_id ? `<span class="badge bg-primary bg-opacity-10 text-primary mb-1">${r.sportif_id}</span><br>` : ''; return `<strong>${v}</strong><br>${idHtml}<small class="text-muted">${r.nik||''}</small>`; } },
            { title:'Gender', data:'gender', defaultContent:'-', render:v=>v==='L'?'<span class="badge bg-primary bg-opacity-10 text-primary">L</span>':v==='P'?'<span class="badge bg-danger bg-opacity-10 text-danger">P</span>':'-' },
            { title:'Domisili', data:'domisili', defaultContent:'-', render:(v)=>v&&v.name?v.name:'-' },
            { title:'Status', data:'status_list', defaultContent:'-', render:(v,t,r)=>{
                const s = r.status_list||r.statusList||[];
                if(!s.length) return '-';
                return s.slice(0,3).map(x=>`<span class="badge bg-success bg-opacity-10 text-success me-1">${x.peran?.nama||''}</span>`).join('')+(s.length>3?'…':'');
            }},
            { title:'Aktif', data:'is_active', render:v=>`<span class="badge-status ${v?'badge-active':'badge-inactive'}">${v?'Aktif':'Non-Aktif'}</span>` },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        tabs: ['Data Pribadi','Status Olahraga','Riwayat Event'],
        filters: [
            {name:'jenis_id',label:'Jenis Domain',type:'select',cache:'_jenisCache',optionKey:'id',optionLabel:'nama',onchange:'adminFilterJenisChanged'},
            {name:'peran_id',label:'Peran',type:'select',cache:'_peranCache',optionKey:'id',optionLabel:'nama'},
            {name:'cabor_id',label:'Cabor',type:'select',cache:'_caborCache',optionKey:'id',optionLabel:'nama'},
            {name:'domisili_id',label:'Kab/Kota',type:'select',cache:'_kabKotaCache',optionKey:'id',optionLabel:'name'},
            {name:'gender',label:'Gender',type:'select',options:[{v:'L',l:'Laki-laki'},{v:'P',l:'Perempuan'}]},
        ],
    },

    // ── PRASARANA ────────────────────────────────────────
    'prasarana': {
        api: '/api/v1/prasarana',
        multiTab: true,
        modalSize: '920px',
        importable: true,
        exportable: true,
        refData: [
            {label:'Lokasi (Kab/Kota)',cache:'_kabKotaCache',nameField:'name'},
            {label:'Jenis',cache:'_jenisCache',nameField:'nama'},
        ],
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Nama', data:'nama', defaultContent:'-' },
            { title:'Kategori', data:'kategori', defaultContent:'-', render:v=>v?`<span class="badge bg-info bg-opacity-10 text-info">${v}</span>`:'-' },
            { title:'Standar', data:'standar', defaultContent:'-' },
            { title:'Lokasi', data:'lokasi', defaultContent:'-', render:(v)=>v&&v.name?v.name:'-' },
            { title:'Pengelola', data:'pengelola', defaultContent:'-', render:v=>`<span class="badge bg-${(v&&v.includes('Pemerintah'))?'primary':'warning'} bg-opacity-10 text-${(v&&v.includes('Pemerintah'))?'primary':'warning'}">${v||'-'}</span>` },
            { title:'Cabor', data:'cabors', defaultContent:'-', render:v=>{if(!v||!v.length) return '-'; return v.slice(0,2).map(c=>`<span class="badge bg-success bg-opacity-10 text-success me-1">${c.nama}</span>`).join('')+(v.length>2?`+${v.length-2}`:'');} },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        tabs: ['Info Dasar','Cabor','Fasilitas','Foto','Peta'],
        filters: [
            {name:'jenis_id',label:'Domain',type:'select',cache:'_jenisCache',optionKey:'id',optionLabel:'nama',onchange:'adminFilterJenisChanged'},
            {name:'cabor_id',label:'Cabor',type:'select',cache:'_caborCache',optionKey:'id',optionLabel:'nama'},
            {name:'kategori',label:'Kategori',type:'select',options:[{v:'Stadion (Sepak bola, atletik)',l:'Stadion'},{v:'Indoor Arena / GOR (Multi-Sport seperti basket, voli, bulu tangkis)',l:'Indoor Arena/GOR'},{v:'Aquatic Center (Kolam renang, loncat indah)',l:'Aquatic Center'},{v:'Velodrome (Balap sepeda)',l:'Velodrome'},{v:'Sirkuit / Lintasan (Motor/mobil)',l:'Sirkuit'},{v:'Lapangan Terbuka / Outdoor Court',l:'Lapangan Terbuka'},{v:'Training Camp / Pusat Pelatihan',l:'Training Camp'},{v:'Gelanggang Pemuda / Youth Center',l:'Gelanggang Pemuda'},{v:'Creative Hub / Coworking Space Pemuda',l:'Creative Hub'},{v:'Bumi Perkemahan (Buper)',l:'Buper'},{v:'Pusat Pendidikan dan Pelatihan (Pusdiklat)',l:'Pusdiklat'}]},
            {name:'standar',label:'Standar',type:'select',options:[{v:'Belum di Standarisasi',l:'Belum di Standarisasi'},{v:'Regional',l:'Regional'},{v:'Nasional',l:'Nasional'},{v:'Internasional',l:'Internasional'}]},
            {name:'lokasi_id',label:'Kab/Kota',type:'select',cache:'_kabKotaCache',optionKey:'id',optionLabel:'name'},
            {name:'pengelola',label:'Pengelola',type:'select',options:[{v:'Pemerintah Kabupaten/Kota',l:'Pemerintah Kab/Kota'},{v:'Pemerintah Provinsi',l:'Pemerintah Provinsi'},{v:'Swasta',l:'Swasta'},{v:'BUMN/BUMD',l:'BUMN/BUMD'},{v:'Kepolisian',l:'Kepolisian'},{v:'Militer',l:'Militer'}]},
        ],
    },

    // ── SARANA ────────────────────────────────────────────
    'sarana': {
        api: '/api/v1/sarana',
        multiTab: true,
        tabs: ['Info Dasar', 'Spesifikasi', 'Lokasi & Status', 'Foto'],
        modalSize: '800px',
        importable: true,
        exportable: true,
        refData: [
            {label:'Jenis',cache:'_jenisCache',nameField:'nama'},
            {label:'Kab/Kota',cache:'_kabKotaCache',nameField:'name'},
        ],
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Barang', data:'nama_barang', render:(v,t,r)=>`<strong>${v}</strong><br><small class="text-muted">Kode: ${r.kode_inventaris||'-'}</small>` },
            { title:'Posisi', data:'posisi_aset', render:(v,t,r)=>`<span class="badge bg-${v==='prasarana'?'info':'secondary'}">${v==='prasarana'?'Prasarana':'Internal Dinas'}</span><br><small class="text-muted">${v==='prasarana'?(r.prasarana?.nama||'-'):(r.keterangan_lokasi||'-')}</small>` },
            { title:'Kondisi', data:'kondisi', render:v=>{const m={baik:'success',rusak_ringan:'warning',rusak_berat:'danger',butuh_perbaikan:'warning',dalam_perbaikan:'info',tidak_layak:'dark'};return `<span class="badge bg-${m[v]||'secondary'}">${v.replace('_',' ').toUpperCase()}</span>`;} },
            { title:'Status', data:'status', render:v=>`<span class="badge bg-light text-dark border">${v.toUpperCase()}</span>` },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        fields: [
            {name:'nama_barang',label:'Nama Barang',type:'text',required:true},
            {name:'kode_inventaris',label:'Kode Inventaris',type:'text'},
            {name:'jenis_id',label:'Domain (Jenis)',type:'select',optionsUrl:'/api/v1/jenis?all',optionKey:'id',optionLabel:'nama'},
            {name:'cabor_id',label:'Cabor',type:'select',optionsUrl:'/api/v1/cabor?all',optionKey:'id',optionLabel:'nama'},
            {name:'kondisi',label:'Kondisi',type:'select',options:[{v:'baik',l:'Baik'},{v:'rusak_ringan',l:'Rusak Ringan'},{v:'rusak_berat',l:'Rusak Berat'},{v:'butuh_perbaikan',l:'Butuh Perbaikan'},{v:'dalam_perbaikan',l:'Dalam Perbaikan'},{v:'tidak_layak',l:'Tidak Layak'}]},
            {name:'status',label:'Status',type:'select',options:[{v:'tersedia',l:'Tersedia'},{v:'dipakai',l:'Dipakai'},{v:'dipinjam',l:'Dipinjam'},{v:'dipelihara',l:'Dipelihara'},{v:'hilang',l:'Hilang'},{v:'rusak_total',l:'Rusak Total'},{v:'dijual',l:'Dijual'},{v:'dimusnahkan',l:'Dimusnahkan'}]},
            {name:'posisi_aset',label:'Posisi Aset',type:'select',options:[{v:'internal_dinas',l:'Internal Dinas'},{v:'prasarana',l:'Prasarana'}]},
            {name:'lokasi_barang',label:'Prasarana (Pilih jika posisi di prasarana)',type:'select',optionsUrl:'/api/v1/prasarana',optionKey:'id',optionLabel:'nama'},
            {name:'keterangan_lokasi',label:'Keterangan Ruangan/Gudang (Jika di dinas)',type:'text'},
            {name:'kab_kota_id',label:'Kab/Kota',type:'select',optionsUrl:'/api/v1/public/kab-kota?all',optionKey:'id',optionLabel:'name'},
            {name:'jumlah',label:'Jumlah',type:'number'},
            {name:'satuan',label:'Satuan (buah, set, dll)',type:'text'},
            {name:'tahun_pengadaan',label:'Tahun Pengadaan',type:'number'},
            {name:'sumber_dana',label:'Sumber Dana',type:'text'},
            {name:'spesifikasi',label:'Spesifikasi Lengkap',type:'textarea',rows:3},
            {name:'foto_barang',label:'Foto Barang (Gambar max 2MB)',type:'file'}
        ],
        filters: [
            {name:'jenis_id',label:'Domain',type:'select',cache:'_jenisCache',optionKey:'id',optionLabel:'nama'},
            {name:'kondisi',label:'Kondisi',type:'select',options:[{v:'baik',l:'Baik'},{v:'rusak_ringan',l:'Rusak Ringan'},{v:'rusak_berat',l:'Rusak Berat'},{v:'butuh_perbaikan',l:'Butuh Perbaikan'},{v:'dalam_perbaikan',l:'Dalam Perbaikan'},{v:'tidak_layak',l:'Tidak Layak'}]},
            {name:'status',label:'Status',type:'select',options:[{v:'tersedia',l:'Tersedia'},{v:'dipakai',l:'Dipakai'},{v:'dipinjam',l:'Dipinjam'},{v:'dipelihara',l:'Dipelihara'},{v:'hilang',l:'Hilang'},{v:'rusak_total',l:'Rusak Total'},{v:'dijual',l:'Dijual'},{v:'dimusnahkan',l:'Dimusnahkan'}]},
            {name:'posisi_aset',label:'Posisi',type:'select',options:[{v:'internal_dinas',l:'Internal Dinas'},{v:'prasarana',l:'Prasarana'}]},
        ],
    },

    // ── EVENT ────────────────────────────────────────────
    'events': {
        api: '/api/v1/events',
        multiTab: true,
        modalSize: '940px',
        importable: true,
        exportable: true,
        refData: [
            {label:'Jenis',cache:'_jenisCache',nameField:'nama'},
            {label:'Skala',cache:'_skalaCache',nameField:'nama'},
        ],
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Nama Event', data:'nama', defaultContent:'-', render:(v,t,r)=>`<strong>${v||'-'}</strong><br><small class="text-muted">${r.penyelenggara||''}</small>` },
            { title:'Jenis', data:'jenis_event', defaultContent:'-', render:v=>v?`<span class="badge bg-info bg-opacity-10 text-info">${v}</span>`:'-' },
            { title:'Domain', data:'jenis', defaultContent:'-', render:(v)=>v&&v.nama?v.nama:'-' },
            { title:'Skala', data:'skala', defaultContent:'-', render:(v)=>v&&v.nama?v.nama:'-' },
            { title:'Tanggal', data:'tanggal_mulai', defaultContent:'-', render:(v,t,r)=>{
                if (!v) return '-';
                const s=new Date(v).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});
                const e=r.tanggal_selesai?new Date(r.tanggal_selesai).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'}):'';
                return `<span class="badge bg-light text-dark border">${s}${e?' - '+e:''}</span>`;
            }},
            { title:'Cabor', data:'cabors', defaultContent:'-', render:v=>{if(!v||!v.length) return '-'; return v.slice(0,2).map(c=>`<span class="badge bg-success bg-opacity-10 text-success me-1">${c.nama}</span>`).join('')+(v.length>2?`+${v.length-2}`:'');} },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        tabs: ['Info Event','Cabor Event'],
        filters: [
            {name:'jenis_id',label:'Domain',type:'select',cache:'_jenisCache',optionKey:'id',optionLabel:'nama',onchange:'adminFilterJenisChanged'},
            {name:'cabor_id',label:'Cabor',type:'select',cache:'_caborCache',optionKey:'id',optionLabel:'nama',disabled:true},
            {name:'jenis_event',label:'Jenis Event',type:'select',options:[{v:'perlombaan',l:'Perlombaan'},{v:'single event',l:'Single Event'},{v:'multi event',l:'Multi Event'},{v:'pelatihan',l:'Pelatihan'}]},
            {name:'skala_id',label:'Skala',type:'select',cache:'_skalaCache',optionKey:'id',optionLabel:'nama'},
            {name:'status',label:'Status',type:'select',options:[{v:'aktif',l:'Aktif'},{v:'selesai',l:'Selesai'},{v:'dibatalkan',l:'Dibatalkan'}]},
            {name:'disabilitas',label:'Disabilitas',type:'select',options:[{v:'1',l:'Ya'},{v:'0',l:'Tidak'}]},
        ],
    },

    // ── ORGANISASI ───────────────────────────────────────
    'organisasi': {
        api: '/api/v1/organisasi',
        importable: true,
        exportable: true,
        refData: [
            {label:'Jenis',cache:'_jenisCache',nameField:'nama'},
            {label:'Skala',cache:'_skalaCache',nameField:'nama'},
            {label:'Kab/Kota',cache:'_kabKotaCache',nameField:'name'},
        ],
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Nama', data:'nama', defaultContent:'-' },
            { title:'Jenis', data:'jenis', defaultContent:'-', render:(v)=>v&&v.nama?v.nama:'-' },
            { title:'Kab/Kota', data:'kab_kota', defaultContent:'-', render:(v)=>v&&v.name?v.name:'-' },
            { title:'Status', data:'status', defaultContent:'-', render:v=>`<span class="badge-status ${v==='Aktif'?'badge-active':'badge-inactive'}">${v||'-'}</span>` },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        multiTab: true,
        tabs: ['Info Dasar', 'Info Tambahan', 'Lokasi Peta'],
        filters: [
            {name:'jenis_id',label:'Jenis Domain',type:'select',cache:'_jenisCache',optionKey:'id',optionLabel:'nama'},
            {name:'kab_kota_id',label:'Kab/Kota',type:'select',cache:'_kabKotaCache',optionKey:'id',optionLabel:'name'},
            {name:'status',label:'Status',type:'select',options:[{v:'Aktif',l:'Aktif'},{v:'Non-Aktif',l:'Non-Aktif'}]},
        ],
    },

    // ── SEKOLAH ──────────────────────────────────────────
    'sekolah': {
        api: '/api/v1/sekolah',
        exportable: true,
        importable: true,
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Nama Sekolah', data:'nama_sekolah', defaultContent:'-', render:(v,t,r)=>`<strong>${v}</strong><br><small class="text-muted">${r.jenis_sekolah||''} ${r.status_sekolah||''}</small>` },
            { title:'Kab/Kota', data:'kab_kota', defaultContent:'-', render:(v)=>v&&v.name?v.name:'-' },
            { title:'Jenis', data:'jenis_sekolah', defaultContent:'-', render:v=>v?`<span class="badge bg-info bg-opacity-10 text-info">${v}</span>`:'-' },
            { title:'Status', data:'status_sekolah', defaultContent:'-', render:v=>v?`<span class="badge bg-${v==='Negeri'?'primary':'warning'} bg-opacity-10 text-${v==='Negeri'?'primary':'warning'}">${v}</span>`:'-' },
            { title:'Jml Ekskul', data:'ekstrakurikuler_count', defaultContent:'0', render:v=>`<span class="badge bg-secondary">${v||0}</span>` },
            { title:'Kontak', data:'narahubung', defaultContent:'-', render:(v,t,r)=>`${v||'-'}<br><small class="text-muted">${r.telepon||'-'}</small>` },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>`<a href="/admin/sekolah/${r.id}" class="btn btn-sm btn-info text-white me-1" title="Detail & Ekskul"><i class="bi bi-eye"></i></a>` + actionBtns(r.id, r.deleted_at) }
        ],
        fields:[
            {name:'nama_sekolah',label:'Nama Sekolah',type:'text',required:true},
            {name:'jenis_sekolah',label:'Jenis Sekolah',type:'select',options:[{v:'SMA',l:'SMA'},{v:'SMK',l:'SMK'},{v:'MA',l:'MA'},{v:'SLB',l:'SLB'}],required:true},
            {name:'status_sekolah',label:'Status Sekolah',type:'select',options:[{v:'Negeri',l:'Negeri'},{v:'Swasta',l:'Swasta'}],required:true},
            {name:'kab_kota_id',label:'Kab/Kota',type:'select',optionsUrl:'/api/v1/public/kab-kota?all',optionKey:'id',optionLabel:'name',required:true},
            {name:'narahubung',label:'Narahubung',type:'text'},
            {name:'telepon',label:'Telepon',type:'text'},
        ],
        filters: [
            {name:'kab_kota_id',label:'Kab/Kota',type:'select',cache:'_kabKotaCache',optionKey:'id',optionLabel:'name'},
            {name:'jenis_sekolah',label:'Jenis Sekolah',type:'select',options:[{v:'SMA',l:'SMA'},{v:'SMK',l:'SMK'},{v:'MA',l:'MA'},{v:'SLB',l:'SLB'}]},
            {name:'status_sekolah',label:'Status Sekolah',type:'select',options:[{v:'Negeri',l:'Negeri'},{v:'Swasta',l:'Swasta'}]},
        ],
    },

    // ── MASTER JENIS EKSTRAKURIKULER ─────────────────────
    'master-jenis-ekstrakurikuler': {
        api: '/api/v1/jenis-ekstrakurikuler',
        importable: true,
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Nama', data:'nama', defaultContent:'-' },
            { title:'Kategori', data:'kategori', defaultContent:'-', render:(v)=>{
                const b = {olahraga:'success', kepemimpinan:'primary', seni_budaya:'danger', akademik_sains:'warning', keagamaan:'info'};
                const l = {olahraga:'Olahraga', kepemimpinan:'Kepemimpinan, Bela Negara & Sosial', seni_budaya:'Seni & Budaya', akademik_sains:'Akademik & Sains', keagamaan:'Keagamaan'};
                return `<span class="badge bg-${b[v]||'secondary'} bg-opacity-10 text-${b[v]||'secondary'}">${l[v]||v}</span>`;
            }},
            { title:'Cabor Terkait', data:'cabor', defaultContent:'-', render:(v)=>v&&v.nama?v.nama:'-' },
            { title:'Keterangan', data:'keterangan', defaultContent:'-' },
            { title:'Aktif', data:'is_active', render:v=>`<span class="badge-status ${v?'badge-active':'badge-inactive'}">${v?'Aktif':'Non-Aktif'}</span>` },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        fields:[
            {name:'nama',label:'Nama Jenis Ekstrakurikuler',type:'text',required:true},
            {name:'kategori',label:'Kategori',type:'select',options:[
                {v:'olahraga',l:'Olahraga'},
                {v:'kepemimpinan',l:'Kepemimpinan, Bela Negara & Sosial'},
                {v:'seni_budaya',l:'Seni & Budaya'},
                {v:'akademik_sains',l:'Akademik & Sains'},
                {v:'keagamaan',l:'Keagamaan'}
            ],required:true},
            {name:'cabor_id',label:'Cabor Terkait (Opsional)',type:'select',optionsUrl:'/api/v1/cabor?all',optionKey:'id',optionLabel:'nama'},
            {name:'keterangan',label:'Keterangan',type:'textarea'},
            {name:'is_active',label:'Status Aktif',type:'select',options:[{v:'1',l:'Aktif'},{v:'0',l:'Non-Aktif'}]},
        ],
        filters: [
            {name:'kategori',label:'Kategori',type:'select',options:[
                {v:'olahraga',l:'Olahraga'},
                {v:'kepemimpinan',l:'Kepemimpinan, Bela Negara & Sosial'},
                {v:'seni_budaya',l:'Seni & Budaya'},
                {v:'akademik_sains',l:'Akademik & Sains'},
                {v:'keagamaan',l:'Keagamaan'}
            ]},
        ],
    },

    // ── INFORMASI ────────────────────────────────────────
    'informasi': {
        api: '/api/v1/informasi',
        exportable: true,
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Judul', data:'judul' },
            { title:'Status', data:'status', render:v=>`<span class="badge-status ${v==='published'?'badge-active':'badge-draft'}">${v}</span>` },
            { title:'Tanggal', data:'created_at', render:v=>new Date(v).toLocaleDateString('id-ID') },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        fields:[
            {name:'judul',label:'Judul',type:'text',required:true},
            {name:'isi',label:'Isi',type:'textarea',rows:6},
            {name:'status',label:'Status',type:'select',options:[{v:'draft',l:'Draft'},{v:'published',l:'Terbit'}]},
        ]
    },

    // ── PENGUMUMAN ───────────────────────────────────────
    'pengumuman': {
        api: '/api/v1/pengumuman',
        exportable: true,
        columns: [
            { title:'#', data:null, orderable:false, render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1, width:'40px' },
            { title:'Judul', data:'judul' },
            { title:'Target', data:'target', render:v=>`<span class="badge bg-info bg-opacity-10 text-info">${v}</span>` },
            { title:'Status', data:'status', render:v=>{const m={active:'badge-active',draft:'badge-draft',expired:'badge-inactive'};return `<span class="badge-status ${m[v]||''}">${v}</span>`;} },
            { title:'Pinned', data:'is_pinned', render:v=>v?'<i class="bi bi-pin-fill text-warning"></i>':'-' },
            { title:'Aksi', data:null, orderable:false, render:(d,t,r)=>actionBtns(r.id, r.deleted_at) }
        ],
        fields:[
            {name:'judul',label:'Judul',type:'text',required:true},
            {name:'isi',label:'Isi',type:'textarea',rows:4},
            {name:'status',label:'Status',type:'select',options:[{v:'draft',l:'Draft'},{v:'active',l:'Aktif'},{v:'expired',l:'Expired'}]},
            {name:'target',label:'Target',type:'select',options:[{v:'all',l:'Semua'},{v:'public',l:'Public'},{v:'admin',l:'Admin'}]},
            {name:'tampil_mulai',label:'Tampil Mulai',type:'datetime-local'},
            {name:'tampil_selesai',label:'Tampil Selesai',type:'datetime-local'},
            {name:'is_pinned',label:'Pinned',type:'select',options:[{v:'0',l:'Tidak'},{v:'1',l:'Ya'}]},
        ]
    },

    // ── MASTER DATA ──────────────────────────────────────
    'master-jenis': {
        api:'/api/v1/jenis',
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Nama Jenis',data:'nama'},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ],
        fields:[{name:'nama',label:'Nama Jenis',type:'text',required:true}]
    },
    'master-peran': {
        api:'/api/v1/peran',
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Nama Peran',data:'nama'},
            {title:'Jenis',data:'jenis.nama',defaultContent:'-'},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ],
        fields:[
            {name:'jenis_id',label:'Jenis',type:'select',optionsUrl:'/api/v1/jenis?all',optionKey:'id',optionLabel:'nama'},
            {name:'nama',label:'Nama Peran',type:'text',required:true},
        ]
    },
    'master-cabor': {
        api:'/api/v1/cabor',
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Nama Cabor',data:'nama'},
            {title:'Tipe',data:'tipe',render:v=>`<span class="badge bg-${v==='olahraga_prestasi'?'primary':'success'} bg-opacity-10 text-${v==='olahraga_prestasi'?'primary':'success'}">${v==='olahraga_prestasi'?'Prestasi':'Masyarakat'}</span>`},
            {title:'Pengprov',data:'nama_pengprov',defaultContent:'-'},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ],
        fields:[
            {name:'nama',label:'Nama Cabang Olahraga',type:'text',required:true},
            {name:'tipe',label:'Tipe',type:'select',options:[{v:'olahraga_prestasi',l:'Olahraga Prestasi (KONI)'},{v:'olahraga_masyarakat',l:'Olahraga Masyarakat (KORMI)'}]},
            {name:'nama_pengprov',label:'Nama Pengprov',type:'text'},
            {name:'keterangan',label:'Keterangan',type:'textarea'},
        ]
    },
    'master-kab-kota': {
        api:'/api/v1/kab-kota',
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Nama',data:'name'},
            {title:'Kode',data:'code'},
            {title:'Tipe',data:'type',render:v=>`<span class="badge bg-${v==='kota'?'primary':'success'} bg-opacity-10 text-${v==='kota'?'primary':'success'}">${v}</span>`},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ],
        fields:[
            {name:'name',label:'Nama',type:'text',required:true},
            {name:'code',label:'Kode BPS',type:'text',required:true},
            {name:'type',label:'Tipe',type:'select',options:[{v:'kabupaten',l:'Kabupaten'},{v:'kota',l:'Kota'}],required:true},
        ]
    },
    'master-skala': {
        api:'/api/v1/skala',
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Nama Skala',data:'nama'},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ],
        fields:[{name:'nama',label:'Nama Skala',type:'text',required:true}]
    },
    'users': {
        api:'/api/v1/users',
        exportable: true,
        multiTab: true,
        tabs: ['Informasi & Role Akses'],
        refData: [
            {label:'Jenis Domain', cache:'_jenisCache', nameField:'nama'},
            {label:'Cabor', cache:'_caborCache', nameField:'nama'},
            {label:'Kab/Kota', cache:'_kabKotaCache', nameField:'name'},
        ],
        filters: [
            {name:'role', label:'Role User', type:'select', options:[
                {v:'SuperAdmin',l:'SuperAdmin'},
                {v:'Admin Dispora Provinsi',l:'Admin Dispora Provinsi'},
                {v:'Admin Bidang Provinsi',l:'Admin Bidang Provinsi'},
                {v:'Admin Koni Provinsi',l:'Admin Koni Provinsi'},
                {v:'Admin Kormi Provinsi',l:'Admin Kormi Provinsi'},
                {v:'Admin NPCI Provinsi',l:'Admin NPCI Provinsi'},
                {v:'Admin Inorga Provinsi',l:'Admin Inorga Provinsi'},
                {v:'Admin Pengprov',l:'Admin Pengprov'},
                {v:'Admin Kwarda',l:'Admin Kwarda'},
                {v:'Kepala Dinas Provinsi',l:'Kepala Dinas Provinsi'},
                {v:'Kepala Bidang Olahraga Prestasi',l:'Kepala Bidang OR Prestasi'},
                {v:'Kepala Bidang Olahraga Masyarakat',l:'Kepala Bidang OR Masyarakat'},
                {v:'Kepala Bidang Kepemudaan',l:'Kepala Bidang Kepemudaan'},
                {v:'Kepala Bidang Kepramukaan',l:'Kepala Bidang Kepramukaan'},
                {v:'Ketua Koni Provinsi',l:'Ketua KONI Provinsi'},
                {v:'Ketua Kormi Provinsi',l:'Ketua KORMI Provinsi'},
                {v:'Ketua Kwarda Provinsi',l:'Ketua Kwarda Provinsi'},
                {v:'Ketua NPCI Provinsi',l:'Ketua NPCI Provinsi'},
                {v:'Ketua Pengprov Cabor',l:'Ketua Pengprov Cabor'},
                {v:'Ketua Inorga Provinsi',l:'Ketua Inorga Provinsi'},
                {v:'Admin Dispora Kab/Kota',l:'Admin Dispora Kab/Kota'},
                {v:'Admin Koni Kab/Kota',l:'Admin Koni Kab/Kota'},
                {v:'Admin Inorga Kab/Kota',l:'Admin Inorga Kab/Kota'},
                {v:'Admin Kwarcab',l:'Admin Kwarcab'},
                {v:'Admin OKP',l:'Admin OKP'},
                {v:'Kepala Dinas Kab/Kota',l:'Kepala Dinas Kab/Kota'},
                {v:'Ketua Koni Kab/Kota',l:'Ketua KONI Kab/Kota'},
                {v:'Ketua Kormi Kab/Kota',l:'Ketua KORMI Kab/Kota'},
                {v:'Ketua NPCI Kab/Kota',l:'Ketua NPCI Kab/Kota'},
                {v:'Ketua Kwarcab Kab/Kota',l:'Ketua Kwarcab Kab/Kota'},
                {v:'Ketua Pengcab Cabor',l:'Ketua Pengcab Cabor'},
                {v:'Ketua Inorga Kab/Kota',l:'Ketua Inorga Kab/Kota'},
            ]},
            {name:'jenis_id', label:'Domain Sistem', type:'select', cache:'_jenisCache', optionKey:'id', optionLabel:'nama'},
            {name:'cabor_id', label:'Cabor / Inorga', type:'select', cache:'_caborCache', optionKey:'id', optionLabel:'nama'},
            {name:'kab_kota_id', label:'Kab/Kota', type:'select', cache:'_kabKotaCache', optionKey:'id', optionLabel:'name'},
            {name:'status', label:'Status', type:'select', options:[{v:1,l:'Aktif'},{v:0,l:'Non-Aktif'}]},
        ],
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Akun',data:null, render:r=>`<strong>${r.name}</strong><br><small class="text-muted"><i class="bi bi-person me-1"></i>${r.username}</small>`},
            {title:'Email',data:'email'},
            {title:'Role',data:'roles',render:v=>v&&v.length?`<span class="badge bg-primary bg-opacity-10 text-primary">${v[0].name}</span>`:'-'},
            {title:'Cakupan',data:null,render:r=>{
                let info = [];
                if(r.jenis) info.push(`<span class="badge bg-light text-dark border">${r.jenis.nama}</span>`);
                if(r.cabor) info.push(`<span class="badge bg-light text-dark border">${r.cabor.nama}</span>`);
                if(r.kabKota) info.push(`<span class="badge bg-light text-dark border">${r.kabKota.name}</span>`);
                return info.length ? info.join(' ') : '<span class="text-muted small">Provinsi (Semua)</span>';
            }},
            {title:'Status',data:'is_active',render:v=>`<span class="badge-status ${v?'badge-active':'badge-inactive'}">${v?'Aktif':'Non-Aktif'}</span>`},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ]
    },
    'log-sistem': {
        api:'/api/v1/log-sistem',
        readOnly:true,
        exportable: true,
        filters:[
            {name:'date_from', label:'Dari Tanggal', type:'date'},
            {name:'date_to', label:'Sampai Tanggal', type:'date'},
            {name:'action', label:'Aksi', type:'select', options:[
                {id:'CREATE',nama:'CREATE'},
                {id:'UPDATE',nama:'UPDATE'},
                {id:'DELETE',nama:'DELETE'},
                {id:'RESTORE',nama:'RESTORE'},
                {id:'IMPORT',nama:'IMPORT'},
                {id:'EXPORT',nama:'EXPORT'},
                {id:'LOGIN',nama:'LOGIN'},
                {id:'LOGOUT',nama:'LOGOUT'}
            ]},
            {name:'module', label:'Modul', type:'select', options:[
                {id:'Auth',nama:'Auth'},
                {id:'Cabor',nama:'Cabor'},
                {id:'User',nama:'User'},
                {id:'Orang',nama:'Orang'},
                {id:'Operator',nama:'Operator'},
                {id:'Organisasi',nama:'Organisasi'},
                {id:'Event',nama:'Event'},
                {id:'Prasarana',nama:'Prasarana'},
                {id:'Sarana',nama:'Sarana'},
                {id:'Sekolah',nama:'Sekolah'},
                {id:'EkstrakurikulerSekolah',nama:'Ekstrakurikuler'},
                {id:'Informasi',nama:'Informasi'},
                {id:'Pengumuman',nama:'Pengumuman'},
                {id:'Dashboard',nama:'Dashboard'}
            ]},
            {name:'user', label:'Nama User', type:'text'}
        ],
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Waktu',data:'created_at',render:v=>new Date(v).toLocaleString('id-ID')},
            {title:'User',data:'user_name'},
            {title:'IP Address',data:'ip_address',defaultContent:'-'},
            {title:'Aksi',data:'action',render:v=>`<span class="badge bg-${v==='CREATE'?'success':v==='UPDATE'?'warning':'danger'} bg-opacity-10 text-${v==='CREATE'?'success':v==='UPDATE'?'warning':'danger'}">${v}</span>`},
            {title:'Modul',data:'module'},
            {title:'Deskripsi',data:'description',defaultContent:'-'},
        ],
        fields:[]
    },
    'operators': {
        api:'/api/v1/operators',
        exportable: true,
        importable: true,
        multiTab: true,
        tabs: ['Data Operator'],
        modalSize: '860px',
        refData: [
            {label:'Skala',cache:'_skalaCache',nameField:'nama'},
            {label:'Cabor',cache:'_caborCache',nameField:'nama'},
            {label:'Kab/Kota',cache:'_kabKotaCache',nameField:'name'},
        ],
        filters: [
            {name:'skala_id',label:'Skala',type:'select',cache:'_skalaCache',optionKey:'id',optionLabel:'nama'},
            {name:'kabkota_id',label:'Kab/Kota',type:'select',cache:'_kabKotaCache',optionKey:'id',optionLabel:'name'},
            {name:'cabor_id',label:'Cabor',type:'select',cache:'_caborCache',optionKey:'id',optionLabel:'nama'},
        ],
        columns:[
            {title:'#',data:null,orderable:false,render:(d,t,r,m)=>m.row+m.settings._iDisplayStart+1,width:'40px'},
            {title:'Operator',data:null, render:r=>`<strong>${r.nama}</strong><br><small class="text-muted"><i class="bi bi-credit-card me-1"></i>${r.nik}</small>`},
            {title:'Jabatan',data:'jabatan',defaultContent:'-'},
            {title:'Role',data:'role',render:v=>v?`<span class="badge bg-primary bg-opacity-10 text-primary">${v.name}</span>`:'-'},
            {title:'Skala',data:'skala',render:v=>v?`<span class="badge bg-info bg-opacity-10 text-info">${v.nama}</span>`:'-'},
            {title:'Kab/Kota',data:'kab_kota',render:v=>v?v.name:'-'},
            {title:'No Telp',data:'no_telp',defaultContent:'-'},
            {title:'Aksi',data:null,orderable:false,render:(d,t,r)=>actionBtns(r.id, r.deleted_at)}
        ]
    },
};

// ═══ CACHE & STATE ════════════════════════════════════════
var _jenisCache=null, _peranCache=null, _caborCache=null, _kabKotaCache=null, _skalaCache=null, _eventsCache=null;
let leafletMap=null, leafletMarker=null;

// ═══ TOAST ════════════════════════════════════════════════
function toast(icon, msg) {
    const id='t'+Date.now();
    const color={'success':'#22c55e','error':'#ef4444','info':'#3b82f6'}[icon]||'#64748b';
    const ico={'success':'check-circle-fill','error':'x-circle-fill','info':'info-circle-fill'}[icon]||'bell';
    document.getElementById('toastBox').insertAdjacentHTML('beforeend',`
        <div id="${id}" style="background:#fff;border-left:4px solid ${color};border-radius:8px;padding:12px 16px;margin-top:8px;box-shadow:0 4px 12px rgba(0,0,0,.15);display:flex;align-items:center;gap:10px">
            <i class="bi bi-${ico}" style="color:${color};font-size:1.2rem"></i>
            <span style="font-size:.875rem">${msg}</span>
        </div>`);
    setTimeout(()=>document.getElementById(id)?.remove(), 3500);
}

// ═══ ACTION BUTTONS ═══════════════════════════════════════
function actionBtns(id, deletedAt = null) {
    if (deletedAt) {
        return `<div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger" style="font-size:0.7rem; padding:4px 6px;">Terhapus</span>
            <button class="btn-action info" onclick="viewRow(${id})" title="Lihat Detail"><i class="bi bi-eye-fill"></i></button>
            <button class="btn-action success" onclick="restoreData(${id})" title="Kembalikan Data"><i class="bi bi-arrow-counterclockwise"></i></button>
        </div>`;
    }
    if (PAGES[pageSlug]?.readOnly || isReadOnly) {
        return `<div class="d-flex gap-1">
            <button class="btn-action info" onclick="viewRow(${id})" title="Lihat Detail"><i class="bi bi-eye-fill"></i></button>
        </div>`;
    }
    return `<div class="d-flex gap-1">
        <button class="btn-action info" onclick="viewRow(${id})" title="Lihat Detail"><i class="bi bi-eye-fill"></i></button>
        <button class="btn-action" onclick="editRow(${id})" title="Edit"><i class="bi bi-pencil-fill"></i></button>
        <button class="btn-action danger" onclick="deleteRow(${id})" title="Hapus"><i class="bi bi-trash-fill"></i></button>
    </div>`;
}

async function restoreData(id) {
    const result = await Swal.fire({
        title: 'Kembalikan Data?',
        text: "Data yang terhapus akan diaktifkan kembali.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#22c55e',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Kembalikan!'
    });
    
    if (result.isConfirmed) {
        try {
            const apiBase = PAGES[pageSlug].api;
            // Endpoint restore kita ada di /api/v1/{model}/{id}/restore
            // Kita bisa mengekstrak model dari apiBase, misalnya '/api/v1/orang' -> 'orang'
            const modelName = apiBase.split('/').pop();
            const res = await fetch(`/api/v1/${modelName}/${id}/restore`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            const text = await res.text();
            let data;
            try { data = JSON.parse(text); } catch (e) {
                if(!res.ok) throw new Error(text || res.statusText);
                throw new Error("Invalid JSON response");
            }
            if(!res.ok) throw new Error(data.message || res.statusText);
            
            showToast('Data berhasil dikembalikan', 'success');
            if(dataTable) dataTable.ajax.reload(null, false);
        } catch (e) {
            Swal.fire('Error', e.message || 'Gagal mengembalikan data', 'error');
        }
    }
}

// ═══ INIT DATATABLE ═══════════════════════════════════════
document.addEventListener('DOMContentLoaded', async () => {
    const cfg = PAGES[pageSlug];
    if (!cfg) return;
    
    if (cfg.readOnly || isReadOnly) {
        document.getElementById('btnTambah')?.remove();
        document.getElementById('btnImport')?.remove();
    } else {
        if (cfg.importable) document.getElementById('btnImport').style.display = '';
    }

    if (cfg.exportable) document.getElementById('btnExport').style.display = '';

    // Start loading caches AND init DataTable in parallel
    const cachePromise = loadCaches();

    // ── Server-side DataTable (fast, handles large datasets) ──
    dataTable = $('#crudTable').DataTable({
        serverSide: true,
        ajax: function(dtParams, callback, settings) {
            const page = Math.floor(dtParams.start / dtParams.length) + 1;
            const params = new URLSearchParams({ page, per_page: dtParams.length });
            const searchInput = document.getElementById('filter_search');
            if (searchInput && searchInput.value) {
                params.set('search', searchInput.value);
            } else if (dtParams.search?.value) {
                params.set('search', dtParams.search.value);
            }
            // Attach filter params
            if (cfg.filters) {
                cfg.filters.forEach(f => {
                    const el = document.getElementById(`filter_${f.name}`);
                    if (el && el.value) params.set(f.name, el.value);
                });
            }
            fetch(`${cfg.api}?${params}`, {
                headers: {'Accept':'application/json','X-CSRF-TOKEN':csrfToken}
            })
            .then(r => r.json())
            .then(json => {
                callback({
                    draw: dtParams.draw,
                    recordsTotal: json.total ?? (json.data||json).length,
                    recordsFiltered: json.total ?? (json.data||json).length,
                    data: json.data || json
                });
            })
            .catch(() => callback({draw: dtParams.draw, recordsTotal:0, recordsFiltered:0, data:[]}));
        },
        columns: cfg.columns,
        processing: true,
        pageLength: 15,
        language: {
            search:'Cari:', lengthMenu:'Tampilkan _MENU_ data', info:'_START_-_END_ dari _TOTAL_',
            emptyTable:'Belum ada data', zeroRecords:'Data tidak ditemukan',
            processing: '<div class="d-flex align-items-center gap-2"><span class="spinner-border spinner-border-sm text-primary"></span> Memuat data...</div>',
            paginate:{first:'«',last:'»',next:'›',previous:'‹'}
        },
        order: [[1,'asc']],
        dom: 'lrtip',
    });

    // Once caches are ready, build the filter bar
    await cachePromise;
    if (cfg.filters && cfg.filters.length) {
        buildFilterBar(cfg.filters);
        document.getElementById('filterBar').style.display = 'block';
    }
});

function reloadTable() { if(dataTable) dataTable.ajax.reload(); }

// ═══ PRE-CACHE MASTER DATA ════════════════════════════════
async function loadCaches() {
    const fetcher = url => fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' }).then(r=>r.json()).then(j=>j.data||j).catch(()=>[]);
    // Load ALL caches in a single parallel batch
    const promises = [
        fetcher('/api/v1/jenis?all'),
        fetcher('/api/v1/peran?all'),
        fetcher('/api/v1/cabor?all'),
        fetcher('/api/v1/public/kab-kota?all'),
        fetcher('/api/v1/skala?all'),
    ];
    // Only fetch events list on the events page (saves a heavy API call on other pages)
    if (pageSlug === 'events') {
        promises.push(fetcher('/api/v1/events'));
    } else if (pageSlug === 'sarana') {
        promises.push(fetcher('/api/v1/prasarana?all'));
    }
    const results = await Promise.all(promises);
    [_jenisCache, _peranCache, _caborCache, _kabKotaCache, _skalaCache] = results;
    if (pageSlug === 'events' && results[5]) {
        _eventsCache = results[5];
    } else if (pageSlug === 'sarana' && results[5]) {
        _prasaranaCache = results[5];
    }
}

function resetFilters() {
    const cfg = PAGES[pageSlug];
    if (cfg.filters) {
        cfg.filters.forEach(f => {
            const el = document.getElementById(`filter_${f.name}`);
            if (el) {
                el.value = '';
                if (f.disabled) {
                    el.disabled = true;
                    el.innerHTML = '<option value="">Pilih Domain Dulu</option>';
                }
            }
        });
    }
    if (document.getElementById('filter_search')) document.getElementById('filter_search').value = '';
    applyAdminFilter();
}

// ═══ CASCADING FILTERS ════════════════════════════════════
function adminFilterJenisChanged(sel) {
    const jenisId = sel.value;
    
    // For Orang: Peran & Cabor
    const peranEl = document.getElementById('filter_peran_id');
    if (peranEl) {
        if (!jenisId) {
            peranEl.disabled = false;
            peranEl.innerHTML = '<option value="">Semua Peran</option>' + 
                (_peranCache||[]).map(p=>`<option value="${p.id}">${p.nama}</option>`).join('');
        } else {
            peranEl.disabled = false;
            peranEl.innerHTML = '<option value="">Semua</option>' + 
                (_peranCache||[]).filter(p=>p.jenis_id==jenisId).map(p=>`<option value="${p.id}">${p.nama}</option>`).join('');
        }
    }

    // For Orang, Prasarana, Event: Cabor
    const caborEl = document.getElementById('filter_cabor_id');
    if (caborEl) {
        if (!jenisId) {
            caborEl.disabled = false;
            caborEl.innerHTML = '<option value="">Semua Cabor</option>' + 
                (_caborCache||[]).map(c=>`<option value="${c.id}">${c.nama}</option>`).join('');
        } else {
            const id = parseInt(jenisId);
            const tipe = id===1?'olahraga_prestasi':id===2?'olahraga_masyarakat':null;
            caborEl.disabled = false;
            
            if (id===3 || id===4) {
                caborEl.innerHTML = '<option value="">Tidak tersedia</option>';
                caborEl.disabled = true;
            } else if (tipe) {
                caborEl.innerHTML = '<option value="">Semua</option>' + 
                    (_caborCache||[]).filter(c=>c.tipe===tipe).map(c=>`<option value="${c.id}">${c.nama}</option>`).join('');
            } else {
                caborEl.innerHTML = '<option value="">Semua</option>' + 
                    (_caborCache||[]).map(c=>`<option value="${c.id}">${c.nama}</option>`).join('');
            }
        }
    }
}

// ═══ FILTER BAR ═══════════════════════════════════════════
function buildFilterBar(filters) {
    const container = document.getElementById('filterFields');
    let html = '';

    filters.forEach(f => {
        let input = '';
        if (f.type === 'select') {
            let opts = `<option value="">Semua</option>`;
            let isKabKotaField = (f.name === 'domisili_id' || f.name === 'lokasi_id' || f.name === 'kab_kota_id');
            let isLocked = isKabKotaField && userKabKotaId;

            if (f.options) {
                f.options.forEach(o => opts += `<option value="${o.v || o.id || ''}">${o.l || o.nama || o.name || ''}</option>`);
            } else if (f.cache) {
                const cacheData = window[f.cache] || eval(f.cache) || [];
                cacheData.forEach(o => {
                    let val = o[f.optionKey];
                    if (isLocked && val != userKabKotaId) return;
                    opts += `<option value="${val}" ${isLocked ? 'selected' : ''}>${o[f.optionLabel]}</option>`;
                });
            }

            if (isLocked) {
                opts = opts.replace('<option value="">Semua</option>', '');
            }

            const onChangeAttr = f.onchange ? `onchange="${f.onchange}(this); applyAdminFilter()"` : `onchange="applyAdminFilter()"`;
            const disabledAttr = (f.disabled || isLocked) ? `disabled` : ``;
            input = `<select class="form-select form-select-sm" id="filter_${f.name}" ${onChangeAttr} ${disabledAttr}>${opts}</select>`;
        } else if (f.type === 'date') {
            input = `<input type="date" class="form-control form-control-sm" id="filter_${f.name}" onchange="applyAdminFilter()">`;
        } else if (f.type === 'text') {
            input = `<input type="text" class="form-control form-control-sm" id="filter_${f.name}" placeholder="Cari..." onkeyup="if(event.key === 'Enter') applyAdminFilter()">`;
        }
        html += `
        <div class="col-md-3 col-sm-6">
            <label class="form-label small text-muted mb-1">${f.label}</label>
            ${input}
        </div>`;
    });

    // Always add search box
    html += `
    <div class="col-md-3 col-sm-6">
        <label class="form-label small text-muted mb-1">Pencarian</label>
        <input type="text" class="form-control form-control-sm" id="filter_search" placeholder="Cari nama..." oninput="debounceAdminFilter()">
    </div>`;

    container.innerHTML = html;
}

let _filterTimer = null;
function debounceAdminFilter() {
    clearTimeout(_filterTimer);
    _filterTimer = setTimeout(applyAdminFilter, 400);
}

function applyAdminFilter() {
    if (dataTable) dataTable.ajax.reload();
}



// ═══ OPEN CREATE MODAL ════════════════════════════════════
async function openCreateModal() {
    editingId = null;
    const cfg = PAGES[pageSlug];
    const header = document.querySelector('#formModal .modal-header');
    header.className = 'modal-header text-white bg-primary bg-gradient';
    document.getElementById('formModalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Tambah Data';
    document.getElementById('formModalDialog').style.maxWidth = cfg.modalSize || '700px';

    if (cfg.multiTab) {
        await buildTabModal(null);
    } else {
        await buildFormFields(null);
    }
    new bootstrap.Modal(document.getElementById('formModal')).show();
}

// ═══ EDIT ROW ═════════════════════════════════════════════
async function editRow(id) {
    const cfg = PAGES[pageSlug];
    editingId = id;
    const header = document.querySelector('#formModal .modal-header');
    header.className = 'modal-header text-white bg-warning bg-gradient';
    document.getElementById('formModalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Data';
    document.getElementById('formModalDialog').style.maxWidth = cfg.modalSize || '700px';

    try {
        const resp = await fetch(`${cfg.api}/${id}`);
        const data = await resp.json();
        if (cfg.multiTab) {
            await buildTabModal(data);
        } else {
            await buildFormFields(data);
        }
        new bootstrap.Modal(document.getElementById('formModal')).show();
    } catch(e) { toast('error','Gagal memuat data'); }
}

// ═══ DELETE ═══════════════════════════════════════════════
function deleteRow(id) {
    Swal.fire({
        title:'Hapus data ini?', text:'Tindakan ini tidak dapat dibatalkan!',
        icon:'warning', showCancelButton:true,
        confirmButtonColor:'#ef4444', cancelButtonText:'Batal', confirmButtonText:'Ya, Hapus!'
    }).then(async res => {
        if (!res.isConfirmed) return;
        const cfg = PAGES[pageSlug];
        const r = await fetch(`${cfg.api}/${id}`, {method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}});
        if (r.ok) { toast('success','Data berhasil dihapus'); reloadTable(); }
        else toast('error','Gagal menghapus data');
    });
}

// ═══ BUILD FORM FIELDS (simple) ═══════════════════════════
async function buildFormFields(data) {
    const cfg = PAGES[pageSlug];
    const container = document.getElementById('formFields');
    document.getElementById('modalTabs').style.setProperty('display','none','important');
    container.innerHTML = '';

    const row = document.createElement('div');
    row.className = 'row g-3';

    for (const f of (cfg.fields||[])) {
        const col = document.createElement('div');
        col.className = f.type==='textarea' ? 'col-12' : 'col-md-6';
        const val = data ? (data[f.name] ?? '') : '';
        let input = '';

        if (f.type === 'select') {
            let opts = '<option value="">— Pilih —</option>';
            let isKabKotaField = (f.name === 'domisili_id' || f.name === 'lokasi_id' || f.name === 'kab_kota_id');
            let isLocked = isKabKotaField && userKabKotaId;

            const list = f.options || (f.optionsUrl ? await fetch(f.optionsUrl).then(r=>r.json()).then(j=>j.data||j).catch(()=>[]) : []);
            list.forEach(o => {
                const v = f.options ? o.v : o[f.optionKey];
                const l = f.options ? o.l : o[f.optionLabel];
                if (isLocked && v != userKabKotaId) return;
                
                let isSelected = String(val)===String(v) || (isLocked && v == userKabKotaId);
                opts += `<option value="${v}" ${isSelected?'selected':''}>${l}</option>`;
            });

            if (isLocked) {
                opts = opts.replace('<option value="">— Pilih —</option>', '');
            }

            input = `<select class="form-select" name="${f.name}" id="f_${f.name}" ${f.required?'required':''} ${(f.disabled || isLocked)?'disabled':''}>${opts}</select>`;
        } else if (f.type==='textarea') {
            input = `<textarea class="form-control" name="${f.name}" id="f_${f.name}" rows="${f.rows||3}" ${f.required?'required':''}>${val}</textarea>`;
        } else {
            input = `<input type="${f.type}" class="form-control" name="${f.name}" id="f_${f.name}" value="${val}" ${f.required?'required':''} ${f.placeholder?`placeholder="${f.placeholder}"`:''}">`;
        }
        col.innerHTML = `<label class="form-label">${f.label}${f.required?'<span class="text-danger ms-1">*</span>':''}</label>${input}`;
        row.appendChild(col);
    }
    container.appendChild(row);
}

// ═══ BUILD TAB MODAL ══════════════════════════════════════
async function buildTabModal(data) {
    const cfg = PAGES[pageSlug];
    const tabs = cfg.tabs;
    const tabsEl = document.getElementById('modalTabs');
    const body   = document.getElementById('formFields');

    tabsEl.style.removeProperty('display');
    tabsEl.innerHTML = tabs.map((t,i)=>`
        <li class="nav-item">
            <button class="nav-link ${i===0?'active':''}" onclick="switchTab(${i})" data-tab="${i}" type="button">${t}</button>
        </li>`).join('');

    body.innerHTML = tabs.map((t,i)=>
        `<div class="tab-content-section ${i===0?'active':''}" id="tabSection_${i}"></div>`
    ).join('');

    if (pageSlug==='orang')      await buildOrangTabs(data);
    if (pageSlug==='prasarana')  await buildPrasaranaTabs(data);
    if (pageSlug==='events')     await buildEventTabs(data);
    if (pageSlug==='organisasi') await buildOrganisasiTabs(data);
    if (pageSlug==='users')      await buildUserTabs(data);
    if (pageSlug==='sarana')     await buildSaranaTabs(data);
    if (pageSlug==='operators')  await buildOperatorTabs(data);
}

function switchTab(idx) {
    document.querySelectorAll('.nav-link[data-tab]').forEach((b,i)=>b.classList.toggle('active',i===idx));
    document.querySelectorAll('.tab-content-section').forEach((s,i)=>s.classList.toggle('active',i===idx));
    if (pageSlug==='prasarana' && idx===4) initLeaflet();
    if (pageSlug==='organisasi' && idx===2) initLeaflet();
}

// ═══════════════════════════════════════════════════════════
// TAB ORANG
// ═══════════════════════════════════════════════════════════
function previewOrangFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('orangFotoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

async function buildOrangTabs(data) {
    // Tab 0: Data Pribadi
    const t0 = document.getElementById('tabSection_0');
    const kabOpts = _kabKotaCache.map(k=>`<option value="${k.id}" ${data?.domisili_id==k.id?'selected':''}>${k.name}</option>`).join('');
    t0.innerHTML = `
    <div class="row g-3">
        <div class="col-md-3 text-center">
            <div class="mb-2">
                <img id="orangFotoPreview" src="${data?.foto ? '/storage/' + data.foto : '/assets/img/default-avatar.png'}" class="img-thumbnail rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
            </div>
            <label class="btn btn-sm btn-outline-primary w-100">
                <i class="bi bi-camera me-1"></i>Pilih Foto
                <input type="file" name="foto" accept="image/*" class="d-none" onchange="previewOrangFoto(this)">
            </label>
        </div>
        <div class="col-md-9 row g-3">
            <div class="col-md-6"><label class="form-label">NIK</label>
                <input class="form-control" name="nik" value="${data?.nik||''}" placeholder="16 Digit"></div>
            <div class="col-md-6"><label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input class="form-control" name="nama" required value="${data?.nama||''}"></div>
            <div class="col-md-6"><label class="form-label">Gender</label>
                <select class="form-select" name="gender">
                    <option value="">—</option>
                    <option value="L" ${data?.gender==='L'?'selected':''}>Laki-laki</option>
                    <option value="P" ${data?.gender==='P'?'selected':''}>Perempuan</option>
                </select></div>
            <div class="col-md-6"><label class="form-label">Tanggal Lahir</label>
                <input type="date" class="form-control" name="tgl_lahir" value="${data?.tgl_lahir?data.tgl_lahir.split('T')[0]:''}"></div>
        </div>
        <div class="col-md-4"><label class="form-label">Gol. Darah</label>
            <select class="form-select" name="gol_darah">
                <option value="">—</option>
                ${['A','B','AB','O'].map(v=>`<option value="${v}" ${data?.gol_darah===v?'selected':''}>${v}</option>`).join('')}
            </select></div>
        <div class="col-md-4"><label class="form-label">Tinggi (cm)</label>
            <input type="number" class="form-control" name="tinggi" step="0.1" value="${data?.tinggi||''}"></div>
        <div class="col-md-4"><label class="form-label">Berat (kg)</label>
            <input type="number" class="form-control" name="berat" step="0.1" value="${data?.berat||''}"></div>
        <div class="col-md-3"><label class="form-label">Disabilitas</label>
            <select class="form-select" name="disabilitas">
                <option value="0" ${!data?.disabilitas?'selected':''}>Tidak</option>
                <option value="1" ${data?.disabilitas?'selected':''}>Ya</option>
            </select></div>
        <div class="col-md-3"><label class="form-label">Jenis Disabilitas</label>
            <select class="form-select" name="jenis_disabilitas">
                <option value="">—</option>
                ${['fisik','intelektual','mental','sensorik_netra','sensorik_rungu','ganda'].map(v=>`<option value="${v}" ${data?.jenis_disabilitas===v?'selected':''}>${v.replace('_',' ').toUpperCase()}</option>`).join('')}
            </select></div>
        <div class="col-md-6"><label class="form-label">No. Telepon</label>
            <input class="form-control" name="telp" value="${data?.telp||''}" placeholder="08xx..."></div>
        <div class="col-md-6"><label class="form-label">Domisili (Kab/Kota)</label>
            <select class="form-select" name="domisili_id">
                <option value="">— Pilih —</option>${kabOpts}
            </select></div>
        <div class="col-12"><label class="form-label">Alamat</label>
            <textarea class="form-control" name="alamat" rows="2">${data?.alamat||''}</textarea></div>
        <div class="col-md-6"><label class="form-label">Status</label>
            <select class="form-select" name="is_active">
                <option value="1" ${data?.is_active!==false?'selected':''}>Aktif</option>
                <option value="0" ${data?.is_active===false?'selected':''}>Non-Aktif</option>
            </select></div>
    </div>`;

    // Tab 1: Status Olahraga (multi-row)
    const t1 = document.getElementById('tabSection_1');
    const existingStatus = data?.status_list || [];
    const hideWriteUI = _isViewMode || isReadOnly;
    
    document.getElementById('tabSection_1').innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted">1 orang dapat memiliki lebih dari 1 status. Setiap status terhubung ke jenis, peran, dan cabor.</small>
        ${hideWriteUI ? '' : `
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addStatusRow()">
            <i class="bi bi-plus-lg me-1"></i>Tambah Status
        </button>`}
    </div>
    <div id="statusContainer">
        ${existingStatus.length ? existingStatus.map((s,i)=>buildStatusRow(s,i)).join('') : buildStatusRow(null,0)}
    </div>`;

    // Tab 2: Riwayat Event (read + tambah)
    const t2 = document.getElementById('tabSection_2');
    if (!data) {
        t2.innerHTML = `<div class="alert alert-info py-2"><i class="bi bi-info-circle me-2"></i>Simpan data orang terlebih dahulu sebelum menambahkan riwayat event.</div>`;
        return;
    }

    // Load riwayat
    let riwayat = [];
    try {
        const r = await fetch(`/api/v1/orang/${data.id}/riwayat`);
        riwayat = await r.json();
    } catch(e) {}

    t2.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-muted small">Riwayat keikutsertaan & prestasi di event.</span>
        ${hideWriteUI ? '' : `
        <button type="button" class="btn btn-sm btn-outline-success" onclick="showRiwayatForm(${data.id})">
            <i class="bi bi-plus-lg me-1"></i>Tambah Riwayat
        </button>`}
    </div>
    <div id="riwayatContainer">
        ${riwayat.length===0 ? '<p class="text-center text-muted py-3">Belum ada riwayat event.</p>'
        : riwayat.map(r=>`
        <div class="riwayat-row mb-2">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>${r.event?.nama||'-'}</strong>
                    <span class="badge ms-2 ${r.medali==='emas'?'medali-emas':r.medali==='perak'?'medali-perak':r.medali==='perunggu'?'medali-perunggu':'bg-secondary'}">${r.medali||'-'}</span>
                </div>
                ${hideWriteUI ? '' : `<button type="button" class="btn-action danger" onclick="deleteRiwayat(${r.id},${data.id})" title="Hapus"><i class="bi bi-trash"></i></button>`}
            </div>
            <small class="text-muted">${r.cabor?.nama||''} · ${r.kategori||''} · ${r.prestasi||''} · ${r.tanggal||''}</small>
        </div>`).join('')}
    </div>

    <div id="riwayatForm" class="mt-3 p-3 border rounded" style="display:none;background:#fffbf0">
        <h6 class="fw-semibold mb-3">Tambah Riwayat Event</h6>
        <div id="riwayatRows">
            ${buildRiwayatRow(0)}
        </div>
        <button type="button" class="btn btn-sm btn-outline-warning mt-2" onclick="addRiwayatRow()">
            <i class="bi bi-plus-lg me-1"></i>Tambah Baris
        </button>
        <div class="d-flex gap-2 mt-3">
            <button type="button" class="btn btn-sm btn-success" onclick="saveRiwayat(${data.id})">
                <i class="bi bi-check me-1"></i>Simpan Riwayat
            </button>
            <button type="button" class="btn btn-sm btn-light" onclick="document.getElementById('riwayatForm').style.display='none'">Batal</button>
        </div>
    </div>`;
}

function buildStatusRow(data, idx) {
    const jenisOpts = _jenisCache.map(j=>`<option value="${j.id}" ${data?.jenis_id==j.id?'selected':''}>${j.nama}</option>`).join('');
    const peranOpts = (_peranCache||[]).filter(p=>!data||p.jenis_id==data.jenis_id).map(p=>`<option value="${p.id}" ${data?.peran_id==p.id?'selected':''}>${p.nama}</option>`).join('');
    const caborOpts = (_caborCache||[]).map(c=>`<option value="${c.id}" ${data?.cabor_id==c.id?'selected':''}>${c.nama}</option>`).join('');
    const skalaOpts = [
        {id:1,nama:'Daerah'},{id:2,nama:'Provinsi'},{id:3,nama:'Nasional'},{id:4,nama:'Internasional'}
    ].map(s=>`<option value="${s.id}" ${data?.skala_id==s.id?'selected':''}>${s.nama}</option>`).join('');

    return `
    <div class="status-row" id="statusRow_${idx}" data-idx="${idx}">
        ${idx>0?`<button type="button" class="remove-row" onclick="removeStatusRow(${idx})" title="Hapus"><i class="bi bi-x-circle"></i></button>`:''}
        <input type="hidden" name="status_id_${idx}" value="${data?.id||''}">
        <div class="row g-2">
            <div class="col-md-4">
                <label class="form-label form-label-sm">Jenis <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="s_jenis_${idx}" onchange="filterPeranCabor(this,${idx})" required>
                    <option value="">— Pilih —</option>${jenisOpts}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Peran <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="s_peran_${idx}" id="peranSel_${idx}" required>
                    <option value="">— Pilih —</option>${peranOpts}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Cabor</label>
                <select class="form-select form-select-sm" name="s_cabor_${idx}" id="caborSel_${idx}">
                    <option value="">— Opsional —</option>${caborOpts}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Skala</label>
                <select class="form-select form-select-sm" name="s_skala_${idx}">
                    <option value="">—</option>${skalaOpts}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">ID Sitenor</label>
                <input class="form-control form-control-sm" name="s_sitenor_${idx}" value="${data?.id_sitenor||''}">
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Sertifikat Profesi</label>
                <input class="form-control form-control-sm" name="s_sertifikat_${idx}" value="${data?.sertifikat_profesi||''}">
            </div>
        </div>
    </div>`;
}

let statusRowCount = 1;
function addStatusRow() {
    document.getElementById('statusContainer').insertAdjacentHTML('beforeend', buildStatusRow(null, statusRowCount++));
}
function removeStatusRow(idx) {
    document.getElementById(`statusRow_${idx}`)?.remove();
}
function filterPeranCabor(jenisSelect, idx) {
    const jenisId = parseInt(jenisSelect.value);
    const peranEl = document.getElementById(`peranSel_${idx}`);
    const caborEl = document.getElementById(`caborSel_${idx}`);
    const filtered = (_peranCache||[]).filter(p=>p.jenis_id===jenisId);
    peranEl.innerHTML = '<option value="">— Pilih —</option>'+filtered.map(p=>`<option value="${p.id}">${p.nama}</option>`).join('');
    // Filter cabor based on jenis
    const tipe = jenisId===1?'olahraga_prestasi':jenisId===2?'olahraga_masyarakat':null;
    const filteredCabor = tipe ? (_caborCache||[]).filter(c=>c.tipe===tipe) : (_caborCache||[]);
    caborEl.innerHTML = '<option value="">— Opsional —</option>'+filteredCabor.map(c=>`<option value="${c.id}">${c.nama}</option>`).join('');
}

// ═══════════════════════════════════════════════════════════
// RIWAYAT EVENT (dalam modal orang)
// ═══════════════════════════════════════════════════════════
let riwayatRowCount = 1;
const eventOpts = () => (_eventsCache||[]).map(e=>`<option value="${e.id}">${e.nama}</option>`).join('');

function buildRiwayatRow(idx) {
    return `
    <div class="status-row mb-2" id="riwayatRow_${idx}">
        ${idx>0?`<button type="button" class="remove-row" onclick="document.getElementById('riwayatRow_${idx}').remove()" title="Hapus"><i class="bi bi-x-circle"></i></button>`:''}
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label form-label-sm">Event <span class="text-danger">*</span></label>
                <select class="form-select form-select-sm" name="rv_event_${idx}" onchange="filterRiwayatCabor(this,${idx})" required>
                    <option value="">— Pilih Event —</option>${eventOpts()}
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label form-label-sm">Cabor</label>
                <select class="form-select form-select-sm" name="rv_cabor_${idx}" id="rvCabor_${idx}">
                    <option value="">— Pilih Event dulu —</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Kategori / Nomor</label>
                <input class="form-control form-control-sm" name="rv_kategori_${idx}" placeholder="-60kg Putra, dll">
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Prestasi</label>
                <input class="form-control form-control-sm" name="rv_prestasi_${idx}" placeholder="Juara 1, dll">
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Medali</label>
                <select class="form-select form-select-sm" name="rv_medali_${idx}">
                    <option value="-">—</option>
                    <option value="emas">🥇 Emas</option>
                    <option value="perak">🥈 Perak</option>
                    <option value="perunggu">🥉 Perunggu</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label form-label-sm">Tanggal</label>
                <input type="date" class="form-control form-control-sm" name="rv_tanggal_${idx}">
            </div>
            <div class="col-md-8">
                <label class="form-label form-label-sm">Keterangan</label>
                <input class="form-control form-control-sm" name="rv_keterangan_${idx}">
            </div>
        </div>
    </div>`;
}

function addRiwayatRow() {
    document.getElementById('riwayatRows').insertAdjacentHTML('beforeend', buildRiwayatRow(riwayatRowCount++));
}

async function filterRiwayatCabor(eventSelect, idx) {
    const eventId = eventSelect.value;
    const el = document.getElementById(`rvCabor_${idx}`);
    if (!eventId) { el.innerHTML='<option value="">— Pilih Event dulu —</option>'; return; }
    try {
        const r = await fetch(`/api/v1/events/${eventId}`);
        const data = await r.json();
        el.innerHTML = '<option value="">— Pilih Cabor —</option>' +
            (data.cabors||[]).map(c=>`<option value="${c.id}">${c.nama}</option>`).join('');
    } catch(e) {}
}

function showRiwayatForm(orangId) {
    document.getElementById('riwayatForm').style.display='block';
}

async function deleteRiwayat(riwayatId, orangId) {
    if (!confirm('Hapus riwayat ini?')) return;
    const r = await fetch(`/api/v1/riwayat-event/${riwayatId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}});
    if (r.ok) { toast('success','Riwayat dihapus'); await buildOrangTabs({id:orangId}); }
    else toast('error','Gagal menghapus');
}

async function saveRiwayat(orangId) {
    const container = document.getElementById('riwayatRows');
    const rows = container.querySelectorAll('[id^="riwayatRow_"]');
    const items = [];

    for (const row of rows) {
        const idx = row.id.split('_')[1];
        const eventId = row.querySelector(`[name="rv_event_${idx}"]`)?.value;
        if (!eventId) continue;
        items.push({
            event_id:  eventId,
            cabor_id:  row.querySelector(`[name="rv_cabor_${idx}"]`)?.value || null,
            kategori:  row.querySelector(`[name="rv_kategori_${idx}"]`)?.value || null,
            prestasi:  row.querySelector(`[name="rv_prestasi_${idx}"]`)?.value || null,
            medali:    row.querySelector(`[name="rv_medali_${idx}"]`)?.value || null,
            tanggal:   row.querySelector(`[name="rv_tanggal_${idx}"]`)?.value || null,
            keterangan:row.querySelector(`[name="rv_keterangan_${idx}"]`)?.value || null,
        });
    }

    if (!items.length) { toast('error','Pilih minimal 1 event'); return; }

    const r = await fetch(`/api/v1/orang/${orangId}/riwayat`,{
        method:'POST',
        headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json','Accept':'application/json'},
        body:JSON.stringify({items})
    });
    const j = await r.json();
    if (r.ok) { toast('success',`${j.message}`); await buildOrangTabs({id:orangId}); }
    else toast('error',j.message||'Gagal menyimpan');
}

// ═══════════════════════════════════════════════════════════
// TAB PRASARANA
// ═══════════════════════════════════════════════════════════
const kategoriMap = {
    '1': ['Stadion (Sepak bola, atletik)', 'Indoor Arena / GOR (Multi-Sport seperti basket, voli, bulu tangkis)', 'Aquatic Center (Kolam renang, loncat indah)', 'Velodrome (Balap sepeda)', 'Sirkuit / Lintasan (Motor/mobil)', 'Lapangan Terbuka / Outdoor Court'],
    '2': ['Stadion (Sepak bola, atletik)', 'Indoor Arena / GOR (Multi-Sport seperti basket, voli, bulu tangkis)', 'Aquatic Center (Kolam renang, loncat indah)', 'Velodrome (Balap sepeda)', 'Sirkuit / Lintasan (Motor/mobil)', 'Lapangan Terbuka / Outdoor Court'],
    '3': ['Training Camp / Pusat Pelatihan', 'Gelanggang Pemuda / Youth Center', 'Creative Hub / Coworking Space Pemuda'],
    '4': ['Bumi Perkemahan (Buper)', 'Pusat Pendidikan dan Pelatihan (Pusdiklat)']
};

function updateKategoriOptions() {
    const jenisSelect = document.getElementById('pras_jenis_id');
    const katSelect = document.getElementById('pras_kategori');
    if(!jenisSelect || !katSelect) return;
    const jenisId = jenisSelect.value;
    const curVal = katSelect.dataset.current || ''; 
    katSelect.innerHTML = '<option value="">— Pilih Kategori —</option>';
    if (jenisId && kategoriMap[jenisId]) {
        kategoriMap[jenisId].forEach(k => {
            const selected = (k === curVal) ? 'selected' : '';
            katSelect.insertAdjacentHTML('beforeend', `<option value="${k}" ${selected}>${k}</option>`);
        });
    }
}

async function buildPrasaranaTabs(data) {
    const kabOpts = _kabKotaCache.map(k=>`<option value="${k.id}" ${data?.lokasi_id==k.id?'selected':''}>${k.name}</option>`).join('');

    // Tab 0: Info Dasar
    document.getElementById('tabSection_0').innerHTML = `
    <div class="row g-4">
        <div class="col-md-12">
            <label class="form-label fw-semibold text-primary"><i class="bi bi-building me-1"></i>Nama Prasarana <span class="text-danger">*</span></label>
            <input class="form-control form-control-lg shadow-sm" name="nama" required value="${data?.nama||''}">
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted"><i class="bi bi-diagram-3 me-1"></i>Domain (Jenis) <span class="text-danger">*</span></label>
            <select class="form-select shadow-sm" name="jenis_id" id="pras_jenis_id" required onchange="updateKategoriOptions()">
                <option value="">— Pilih Domain —</option>
                ${_jenisCache.map(j=>`<option value="${j.id}" ${data?.jenis_id==j.id?'selected':''}>${j.nama}</option>`).join('')}
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted"><i class="bi bi-tags me-1"></i>Kategori</label>
            <select class="form-select shadow-sm" name="kategori" id="pras_kategori" data-current="${data?.kategori||''}">
                <option value="">— Pilih Kategori —</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted"><i class="bi bi-star me-1"></i>Standar</label>
            <select class="form-select shadow-sm" name="standar">
                ${['Belum di Standarisasi','Regional','Nasional','Internasional'].map(s=>`<option value="${s}" ${data?.standar===s?'selected':''}>${s}</option>`).join('')}
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted"><i class="bi bi-person-badge me-1"></i>Pengelola</label>
            <select class="form-select shadow-sm" name="pengelola">
                <option value="">— Pilih —</option>
                ${['Pemerintah Kabupaten/Kota','Pemerintah Provinsi','Swasta','BUMN/BUMD','Kepolisian','Militer'].map(o=>`<option value="${o}" ${data?.pengelola===o?'selected':''}>${o}</option>`).join('')}
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted"><i class="bi bi-geo-alt me-1"></i>Lokasi (Kab/Kota)</label>
            <select class="form-select shadow-sm" name="lokasi_id"><option value="">— Pilih —</option>${kabOpts}</select>
        </div>
        <div class="col-md-4">
            <label class="form-label text-muted"><i class="bi bi-people me-1"></i>Kapasitas</label>
            <input type="number" class="form-control shadow-sm" name="kapasitas" value="${data?.kapasitas||''}" placeholder="Jumlah penonton">
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted"><i class="bi bi-person me-1"></i>Narahubung</label>
            <input class="form-control shadow-sm" name="narahubung" value="${data?.narahubung||''}">
        </div>
        <div class="col-md-6">
            <label class="form-label text-muted"><i class="bi bi-telephone me-1"></i>Telepon Narahubung</label>
            <input class="form-control shadow-sm" name="telp_narahubung" value="${data?.telp_narahubung||''}">
        </div>
        <div class="col-12">
            <label class="form-label text-muted"><i class="bi bi-map me-1"></i>Alamat Lengkap</label>
            <textarea class="form-control shadow-sm" name="alamat" rows="2">${data?.alamat||''}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label text-muted"><i class="bi bi-info-circle me-1"></i>Keterangan Tambahan</label>
            <textarea class="form-control shadow-sm" name="keterangan" rows="2">${data?.keterangan||''}</textarea>
        </div>
    </div>`;
    setTimeout(() => updateKategoriOptions(), 50);

    // Tab 1: Cabor (multi-select)
    const selectedCabors = (data?.cabors||[]).map(c=>c.id);
    document.getElementById('tabSection_1').innerHTML = `
    <div class="alert alert-info py-2 mb-3 small"><i class="bi bi-info-circle me-2"></i>Pilih cabang olahraga yang didukung oleh prasarana ini (bisa lebih dari satu).</div>
    <div class="d-flex flex-wrap gap-2" id="caborCheckList">
        ${(_caborCache||[]).map(c=>`
        <div>
            <input type="checkbox" class="btn-check" name="cabor_ids[]" value="${c.id}" id="cbCabor_${c.id}" ${selectedCabors.includes(c.id)?'checked':''}>
            <label class="btn btn-outline-primary rounded-pill btn-sm px-3" for="cbCabor_${c.id}">${c.nama}</label>
        </div>`).join('')}
    </div>`;

    const hideWriteUI = _isViewMode || isReadOnly;
    
    // Tab 2: Fasilitas (multi-row, struktur baru: nama, jumlah, kondisi, keterangan)
    const existFasilitas = data?.fasilitas || [];
    
    if (hideWriteUI) {
        document.getElementById('tabSection_2').innerHTML = `
        <div class="table-responsive">
            <table class="table table-bordered table-striped small">
                <thead class="table-light">
                    <tr><th>Nama Fasilitas</th><th>Jumlah</th><th>Kondisi</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                    ${existFasilitas.length ? existFasilitas.map(f=>`
                    <tr>
                        <td>${f.nama||'-'}</td>
                        <td>${f.jumlah||'-'}</td>
                        <td>${f.kondisi||'-'}</td>
                        <td>${f.keterangan||'-'}</td>
                    </tr>`).join('') : '<tr><td colspan="4" class="text-center text-muted">Tidak ada fasilitas</td></tr>'}
                </tbody>
            </table>
        </div>`;
    } else {
        document.getElementById('tabSection_2').innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-3">
            <small class="text-muted">Daftar fasilitas yang ada di dalam prasarana ini.</small>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addFasilitasRow()">
                <i class="bi bi-plus-lg me-1"></i>Tambah Fasilitas
            </button>
        </div>
        <div id="fasilitasContainer">
            ${existFasilitas.length ? existFasilitas.map((f,i)=>buildFasilitasRow(f,i)).join('') : buildFasilitasRow(null,0)}
        </div>`;
    }

    // Tab 3: Foto (multi upload)
    const existFotos = data?.fotos || [];
    document.getElementById('tabSection_3').innerHTML = `
    ${hideWriteUI ? '' : `
    <div class="card bg-light border-0 mb-4 shadow-sm">
        <div class="card-body text-center py-4">
            <i class="bi bi-images text-muted fs-1 mb-2 d-block"></i>
            <h6 class="fw-semibold">Upload Foto Prasarana</h6>
            <p class="text-muted small mb-3">Bisa memilih lebih dari 1 foto sekaligus (Maks 2MB/foto)</p>
            <label class="btn btn-outline-primary px-4">
                <i class="bi bi-cloud-arrow-up me-2"></i>Pilih File
                <input type="file" class="d-none" id="fotoUpload" accept="image/*" multiple onchange="previewFotos(this)">
            </label>
        </div>
    </div>`}
    <div id="fotoPreviewList" class="row g-3">
        ${existFotos.map(f=>`
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden group">
                <img src="/storage/${f.foto}" class="card-img-top" style="height:140px; object-fit:cover;">
                ${hideWriteUI ? '' : `
                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 shadow-sm rounded-circle" onclick="deleteFoto(${f.id})" style="width:30px;height:30px;padding:0;">
                    <i class="bi bi-trash"></i>
                </button>`}
            </div>
        </div>`).join('')}
        ${hideWriteUI && existFotos.length===0 ? '<div class="col-12 text-center text-muted py-4"><i class="bi bi-camera-fill fs-2 d-block mb-2 text-light"></i>Belum ada foto prasarana</div>' : ''}
    </div>`;

    // Tab 4: Peta
    document.getElementById('tabSection_4').innerHTML = `
    <p class="text-muted small">Klik pada peta untuk memilih lokasi koordinat prasarana.</p>
    <div class="row g-2 mb-2">
        <div class="col-md-5"><label class="form-label form-label-sm">Latitude</label>
            <input class="form-control form-control-sm" name="latitude" id="latInput" value="${data?.latitude||''}" placeholder="-7.xxxx" ${hideWriteUI?'readonly':''}></div>
        <div class="col-md-5"><label class="form-label form-label-sm">Longitude</label>
            <input class="form-control form-control-sm" name="longitude" id="lngInput" value="${data?.longitude||''}" placeholder="112.xxxx" ${hideWriteUI?'readonly':''}></div>
        ${hideWriteUI && data?.latitude && data?.longitude ? `
        <div class="col-md-2 d-flex align-items-end">
            <a href="https://www.google.com/maps?q=${data.latitude},${data.longitude}" target="_blank" class="btn btn-sm btn-outline-success w-100">
                <i class="bi bi-geo-alt me-1"></i> Buka G-Maps
            </a>
        </div>` : ''}
    </div>
    <div id="leafletMap" style="height:320px;border-radius:8px;border:1px solid #e2e8f0; ${hideWriteUI ? 'pointer-events: none;' : ''}"></div>`;
}

let fasilitasRowCount = 1;
function buildFasilitasRow(data, idx) {
    return `
    <div class="card border border-light shadow-sm mb-3 position-relative" id="fasRow_${idx}">
        ${idx>0?`<button type="button" class="btn btn-danger btn-sm position-absolute rounded-circle shadow-sm" style="top:-10px; right:-10px; width:28px; height:28px; padding:0; z-index:10;" onclick="document.getElementById('fasRow_${idx}').remove()" title="Hapus"><i class="bi bi-x"></i></button>`:''}
        <div class="card-body p-3">
            <input type="hidden" name="fas_id_${idx}" value="${data?.id||''}">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small text-muted mb-1">Nama Fasilitas <span class="text-danger">*</span></label>
                    <input class="form-control form-control-sm shadow-sm" name="fas_nama_${idx}" required value="${data?.nama||''}" placeholder="Contoh: Lapangan Utama, Toilet, Tribun">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1">Kondisi</label>
                    <select class="form-select form-select-sm shadow-sm" name="fas_kondisi_${idx}">
                        <option value="Baik" ${data?.kondisi==='Baik'?'selected':''}>🟢 Baik</option>
                        <option value="Rusak Ringan" ${data?.kondisi==='Rusak Ringan'?'selected':''}>🟡 Rusak Ringan</option>
                        <option value="Rusak Berat" ${data?.kondisi==='Rusak Berat'?'selected':''}>🔴 Rusak Berat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1">Jumlah</label>
                    <input type="number" class="form-control form-control-sm shadow-sm" name="fas_jumlah_${idx}" value="${data?.jumlah||1}" min="1">
                </div>
                <div class="col-md-12 mt-2">
                    <input class="form-control form-control-sm shadow-sm border-0 bg-light" name="fas_keterangan_${idx}" value="${data?.keterangan||''}" placeholder="Catatan opsional tentang fasilitas ini...">
                </div>
            </div>
        </div>
    </div>`;
}
function addFasilitasRow() {
    document.getElementById('fasilitasContainer').insertAdjacentHTML('beforeend', buildFasilitasRow(null, fasilitasRowCount++));
}

function previewFotos(input) {
    const prev = document.getElementById('fotoPreviewList');
    // Jika tidak ada foto (berisi placeholder 'Belum ada foto'), hapus dulu isinya
    if (prev.innerHTML.includes('Belum ada foto')) prev.innerHTML = '';
    
    Array.from(input.files).forEach(file => {
        const url = URL.createObjectURL(file);
        prev.insertAdjacentHTML('beforeend', `
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <img src="${url}" class="card-img-top" style="height:140px; object-fit:cover;">
                <div class="card-footer p-2 text-truncate small text-muted bg-white border-0">${file.name}</div>
            </div>
        </div>`);
    });
}

async function deleteFoto(fotoId) {
    if (!confirm('Hapus foto ini?')) return;
    const r = await fetch(`/api/v1/prasarana/foto/${fotoId}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}});
    if (r.ok) toast('success','Foto dihapus');
    else toast('error','Gagal hapus foto');
}

function initLeaflet() {
    if (leafletMap) return;
    const lat = parseFloat(document.getElementById('latInput')?.value) || -7.25;
    const lng = parseFloat(document.getElementById('lngInput')?.value) || 112.75;
    leafletMap = L.map('leafletMap').setView([lat, lng], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(leafletMap);
    if (lat !== -7.25) {
        leafletMarker = L.marker([lat, lng]).addTo(leafletMap);
    }
    leafletMap.on('click', e => {
        document.getElementById('latInput').value = e.latlng.lat.toFixed(6);
        document.getElementById('lngInput').value = e.latlng.lng.toFixed(6);
        if (leafletMarker) leafletMap.removeLayer(leafletMarker);
        leafletMarker = L.marker(e.latlng).addTo(leafletMap);
    });
    setTimeout(()=>leafletMap.invalidateSize(), 100);
}

// ═══════════════════════════════════════════════════════════
// TAB EVENT
// ═══════════════════════════════════════════════════════════
async function buildEventTabs(data) {
    const skalaOpts = [
        {id:1,nama:'Daerah'},{id:2,nama:'Provinsi'},{id:3,nama:'Nasional'},{id:4,nama:'Internasional'}
    ].map(s=>`<option value="${s.id}" ${data?.skala_id==s.id?'selected':''}>${s.nama}</option>`).join('');
    const jenisOpts = (_jenisCache||[]).map(j=>`<option value="${j.id}" ${data?.jenis_id==j.id?'selected':''}>${j.nama}</option>`).join('');

    // Tab 0: Info Event
    document.getElementById('tabSection_0').innerHTML = `
    <div class="row g-4">
        <div class="col-md-12">
            <label class="form-label fw-semibold"><i class="bi bi-bookmark-star me-1"></i>Nama Event <span class="text-danger">*</span></label>
            <input class="form-control form-control-lg shadow-sm" name="nama" required value="${data?.nama||''}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Domain Sistem <span class="text-danger">*</span></label>
            <select class="form-select shadow-sm" name="jenis_id" id="evJenisId" onchange="onEventInfoChange()" required>
                <option value="">— Pilih Domain —</option>${jenisOpts}
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-tags me-1"></i>Jenis Event <span class="text-danger">*</span></label>
            <select class="form-select shadow-sm" name="jenis_event" id="evJenisEvent" onchange="onEventInfoChange()" required>
                <option value="single event" ${data?.jenis_event==='single event'?'selected':''}>Single Event (1 Cabor)</option>
                <option value="multi event" ${data?.jenis_event==='multi event'?'selected':''}>Multi Event (Banyak Cabor)</option>
                <option value="perlombaan" ${data?.jenis_event==='perlombaan'?'selected':''}>Perlombaan Khusus</option>
                <option value="pelatihan" ${data?.jenis_event==='pelatihan'?'selected':''}>Pelatihan</option>
            </select>
        </div>
        
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-globe me-1"></i>Skala Event</label>
            <select class="form-select" name="skala_id"><option value="">— Pilih Skala —</option>${skalaOpts}</select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-toggle-on me-1"></i>Status</label>
            <select class="form-select" name="status">
                <option value="aktif" ${data?.status==='aktif'?'selected':''}>🟢 Aktif / Berlangsung</option>
                <option value="selesai" ${data?.status==='selesai'?'selected':''}>⚪ Selesai</option>
                <option value="dibatalkan" ${data?.status==='dibatalkan'?'selected':''}>🔴 Dibatalkan</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-person-wheelchair me-1"></i>Event Disabilitas</label>
            <select class="form-select" name="disabilitas">
                <option value="0" ${!data?.disabilitas?'selected':''}>Tidak</option>
                <option value="1" ${data?.disabilitas?'selected':''}>Ya</option>
            </select>
        </div>
        <div class="col-12"><hr class="my-2"></div>
        <div class="col-md-12">
            <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i>Instansi Penyelenggara <span class="text-danger">*</span></label>
            <input class="form-control shadow-sm" name="penyelenggara" required value="${data?.penyelenggara||''}">
        </div>
        <div class="col-md-12">
            <label class="form-label fw-semibold"><i class="bi bi-geo-alt me-1"></i>Lokasi Kegiatan</label>
            <input class="form-control" name="lokasi_kegiatan" value="${data?.lokasi_kegiatan||''}" placeholder="Contoh: Stadion Utama, Gor Kota, dll">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-calendar-event me-1"></i>Tanggal Mulai</label>
            <input type="date" class="form-control shadow-sm" name="tanggal_mulai" value="${data?.tanggal_mulai||''}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-calendar-check me-1"></i>Tanggal Selesai</label>
            <input type="date" class="form-control shadow-sm" name="tanggal_selesai" value="${data?.tanggal_selesai||''}">
        </div>
    </div>`;

    // Tab 1: Cabor Event
    const selectedCabors = (data?.cabors||[]).map(c=>c.id);
    const hideWriteUI = _isViewMode || isReadOnly;
    window._tempEventCabors = selectedCabors;
    window._tempEventHideWriteUI = hideWriteUI;
    setTimeout(() => onEventInfoChange(), 50);
}

function onEventInfoChange() {
    const jenisId = document.getElementById('evJenisId')?.value;
    const eventType = document.getElementById('evJenisEvent')?.value || 'multi event';
    const isMulti = eventType !== 'single event';
    const tipe = jenisId == 1 ? 'olahraga_prestasi' : jenisId == 2 ? 'olahraga_masyarakat' : null;
    
    const filteredCabor = tipe ? (_caborCache||[]).filter(c=>c.tipe===tipe) : (_caborCache||[]);
    const selectedCabors = window._tempEventCabors || [];
    const hideWriteUI = window._tempEventHideWriteUI;
    
    const container = document.getElementById('tabSection_1');
    if (!container) return;
    
    let tab1HTML = '';
    if (hideWriteUI) {
        const selectedNames = filteredCabor
            .filter(c => selectedCabors.includes(c.id))
            .map(c => `<span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 me-2 mb-2 fs-6 border border-primary"><i class="bi bi-trophy me-1"></i>${c.nama}</span>`);
        tab1HTML = selectedNames.length 
            ? `<div class="d-flex flex-wrap gap-1 p-3">${selectedNames.join('')}</div>`
            : '<div class="text-center text-muted py-4">Tidak ada cabor terdaftar</div>';
    } else {
        const inputType = isMulti ? 'checkbox' : 'radio';
        const nameAttr = 'cabor_ids[]';
        const infoMsg = tipe 
            ? (isMulti ? 'Pilih beberapa cabor yang dipertandingkan.' : 'Anda hanya dapat memilih 1 cabor (Single Event).') 
            : 'Silakan pilih Domain Sistem (Prestasi/Masyarakat) di Tab Info Dasar terlebih dahulu.';
            
        tab1HTML = `
        <div class="alert alert-${tipe?'info':'warning'} py-2 mb-4 d-flex align-items-center">
            <i class="bi bi-info-circle-fill me-2 fs-5"></i>
            <div>
                <strong>${tipe ? (tipe==='olahraga_prestasi'?'Olahraga Prestasi':'Olahraga Masyarakat') : 'Menunggu Domain Sistem'}</strong><br>
                <small>${infoMsg}</small>
            </div>
        </div>
        <div class="row g-3" id="eventCaborList">
            ${filteredCabor.map(c=>`
            <div class="col-md-4">
                <div class="form-check p-2 border rounded shadow-sm bg-white" style="cursor:pointer;" onclick="document.getElementById('evCabor_${c.id}').click()">
                    <input class="form-check-input ms-1 me-2" type="${inputType}" name="${nameAttr}" value="${c.id}" id="evCabor_${c.id}" ${selectedCabors.includes(c.id)?'checked':''} onclick="event.stopPropagation()">
                    <label class="form-check-label fw-medium" for="evCabor_${c.id}">${c.nama}</label>
                </div>
            </div>`).join('')}
            ${filteredCabor.length===0 && tipe ? '<div class="col-12 text-muted text-center py-4"><i class="bi bi-inbox fs-1 d-block mb-2 text-black-50"></i>Belum ada data cabor di domain ini.</div>' : ''}
        </div>`;
    }
    
    container.innerHTML = tab1HTML;
}



async function onEventJenisChange() {
    const jenisId = parseInt(document.getElementById('evJenisId')?.value);
    const tipe = jenisId===1?'olahraga_prestasi':jenisId===2?'olahraga_masyarakat':null;
    const filteredCabor = tipe ? (_caborCache||[]).filter(c=>c.tipe===tipe) : (_caborCache||[]);
    document.getElementById('caborTabInfo').textContent = tipe?`(${tipe==='olahraga_prestasi'?'Olahraga Prestasi':'Olahraga Masyarakat'})`:'';
    const el = document.getElementById('eventCaborList');
    if (el) el.innerHTML = filteredCabor.map(c=>`
        <div class="col-md-4"><div class="form-check">
            <input class="form-check-input" type="checkbox" name="cabor_ids[]" value="${c.id}" id="evCabor_${c.id}">
            <label class="form-check-label small" for="evCabor_${c.id}">${c.nama}</label>
        </div></div>`).join('') || '<div class="col-12 text-muted text-center py-3">Tidak ada cabor</div>';
}

// ═══ FORM SUBMIT ════════════════════════════════════════
let isFormSubmitting = false;
document.getElementById('crudForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (isFormSubmitting) return;
    
    const cfg = PAGES[pageSlug];
    const formEl = this;
    const btnSave = document.getElementById('btnSave');

    isFormSubmitting = true;
    if (btnSave) {
        btnSave.disabled = true;
        btnSave.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';
    }

    try {
        if (cfg.multiTab) {
            await submitMultiTabForm();
        } else {
            const hasFile = formEl.querySelector('input[type="file"]');
            if (hasFile) {
                const formData = new FormData(formEl);
                let url = editingId ? `${cfg.api}/${editingId}` : cfg.api;
                if (editingId) formData.append('_method', 'PUT');
                
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN':csrfToken, 'Accept':'application/json'},
                    body: formData
                });
                
                if (r.ok) {
                    bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
                    toast('success', editingId ? 'Data berhasil diperbarui' : 'Data berhasil ditambahkan');
                    reloadTable();
                } else {
                    const err = await r.json();
                    const msg = err.errors ? Object.values(err.errors).flat().join('<br>') : (err.message||'Gagal menyimpan');
                    Swal.fire({icon:'error',title:'Validasi Gagal',html:msg});
                }
            } else {
                const data = Object.fromEntries(new FormData(formEl));
                if (editingId && !data.password) delete data.password;
                await submitSimple(cfg.api, data);
            }
        }
    } catch (err) {
        Swal.fire({icon:'error',title:'Terjadi Kesalahan',text:'Gagal menghubungi server'});
    } finally {
        isFormSubmitting = false;
        if (btnSave) {
            btnSave.disabled = false;
            btnSave.innerHTML = '<i class="bi bi-check-lg me-1"></i>Simpan';
        }
    }
});

async function submitSimple(api, data) {
    const url = editingId ? `${api}/${editingId}` : api;
    const method = editingId ? 'PUT' : 'POST';
    const r = await fetch(url, {method, headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json','Accept':'application/json'}, body:JSON.stringify(data)});
    if (r.ok) {
        bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
        toast('success', editingId?'Data berhasil diperbarui':'Data berhasil ditambahkan');
        reloadTable();
    } else {
        const err = await r.json();
        const msg = err.errors ? Object.values(err.errors).flat().join('<br>') : (err.message||'Gagal menyimpan');
        Swal.fire({icon:'error',title:'Validasi Gagal',html:msg});
    }
}

// ═══ CUSTOM TABS UNTUK USER ════════════════════════════════
async function buildUserTabs(data) {
    const isReadOnly = _isViewMode;
    
    // Tab 1: Info User
    const tab1 = document.getElementById('tabSection_0');
    const roles = PAGES['users'].filters[0].options;
    const jenisOpts = _jenisCache || [];
    const caborOpts = _caborCache || [];
    const kabOpts = _kabKotaCache || [];
    
    tab1.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="${data?.name||''}" required ${isReadOnly?'disabled':''}>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control" value="${data?.username||''}" required ${isReadOnly?'disabled':''}>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="${data?.email||''}" required ${isReadOnly?'disabled':''}>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Password ${data?'(Kosongkan jika tidak diubah)':'<span class="text-danger">*</span>'}</label>
                <input type="password" name="password" class="form-control" ${isReadOnly?'disabled':''} ${!data?'required':''}>
            </div>
            <div class="col-md-12">
                <hr>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Role Akses <span class="text-danger">*</span></label>
                <select name="role" id="userRole" class="form-select" required ${isReadOnly?'disabled':''}>
                    <option value="">-- Pilih Role --</option>
                    ${roles.map(r=>`<option value="${r.v}" ${data?.roles?.[0]?.name===r.v?'selected':''}>${r.l}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-end">
                <div class="form-check form-switch ms-2">
                    <input class="form-check-input" type="checkbox" name="is_active" id="userActive" value="1" ${data ? (data.is_active ? 'checked' : '') : 'checked'} ${isReadOnly?'disabled':''}>
                    <label class="form-check-label" for="userActive">Status Aktif</label>
                </div>
            </div>
            
            <!-- Dynamic Scopes -->
            <div class="col-md-4" id="wrapJenis" style="display:none;">
                <label class="form-label fw-bold text-primary">Domain Sistem <span class="text-danger">*</span></label>
                <select name="jenis_id" id="userJenis" class="form-select" ${isReadOnly?'disabled':''}>
                    <option value="">-- Pilih --</option>
                    ${jenisOpts.map(j=>`<option value="${j.id}" ${data?.jenis_id===j.id?'selected':''}>${j.nama}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4" id="wrapKabKota" style="display:none;">
                <label class="form-label fw-bold text-success">Kab/Kota <span class="text-danger">*</span></label>
                <select name="kab_kota_id" id="userKabKota" class="form-select select2-init" ${isReadOnly?'disabled':''}>
                    <option value="">-- Pilih --</option>
                    ${kabOpts.map(k=>`<option value="${k.id}" ${data?.kab_kota_id===k.id?'selected':''}>${k.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4" id="wrapCabor" style="display:none;">
                <label class="form-label fw-bold text-warning">Cabor/Inorga <span class="text-danger">*</span></label>
                <select name="cabor_id" id="userCabor" class="form-select select2-init" ${isReadOnly?'disabled':''}>
                    <option value="">-- Pilih --</option>
                    ${caborOpts.map(c=>`<option value="${c.id}" ${data?.cabor_id===c.id?'selected':''}>${c.nama}</option>`).join('')}
                </select>
            </div>
        </div>
    `;

    // Dynamic Logic
    const roleEl = document.getElementById('userRole');
    const jenisEl = document.getElementById('userJenis');
    const caborEl = document.getElementById('userCabor');
    const wrapJenis = document.getElementById('wrapJenis');
    const wrapKab = document.getElementById('wrapKabKota');
    const wrapCabor = document.getElementById('wrapCabor');
    
    function toggleScopes() {
        const r = roleEl.value || '';
        const jId = jenisEl.value || '';

        const isDaerah = r.includes('Kab/Kota') || r.includes('Kwarcab') || r.includes('Pengcab');
        const isCaborRole = r.includes('Cabor') || r.includes('Inorga') || r.includes('NPCI');
        const isDomain = r.includes('Koni') || r.includes('Kormi') || r.includes('NPCI') || r.includes('Dispora') || r.includes('Inorga') || r.includes('Cabor') || r.includes('Pengprov') || r.includes('Pengcab');
        
        // Cabor muncul jika jenis sistem yang dipilih adalah Olahraga Prestasi (1) atau Olahraga Masyarakat (2)
        const isCabor = (jId == 1 || jId == 2);

        wrapKab.style.display = isDaerah ? 'block' : 'none';
        wrapJenis.style.display = isDomain ? 'block' : 'none';
        wrapCabor.style.display = isCabor ? 'block' : 'none';
        
        // Filter dropdown Cabor sesuai Domain Sistem
        if (jId == 1 || jId == 2) {
            const tipeMap = { 1: 'olahraga_prestasi', 2: 'olahraga_masyarakat' };
            const filtered = caborOpts.filter(c => c.tipe === tipeMap[jId]);
            const currentVal = caborEl.value;
            caborEl.innerHTML = '<option value="">-- Pilih --</option>' + 
                filtered.map(c => `<option value="${c.id}" ${currentVal == c.id ? 'selected' : ''}>${c.nama}</option>`).join('');
        } else {
            const currentVal = caborEl.value;
            caborEl.innerHTML = '<option value="">-- Pilih --</option>' + 
                caborOpts.map(c => `<option value="${c.id}" ${currentVal == c.id ? 'selected' : ''}>${c.nama}</option>`).join('');
        }
    }

    if(roleEl) roleEl.addEventListener('change', toggleScopes);
    if(jenisEl) jenisEl.addEventListener('change', toggleScopes);
    
    toggleScopes();
    
}

async function buildOperatorTabs(data) {
    const isRO = _isViewMode;
    const tab0 = document.getElementById('tabSection_0');
    const skalaOpts = _skalaCache || [];
    const caborOpts = _caborCache || [];
    const kabOpts = _kabKotaCache || [];
    
    // Fetch roles list
    let rolesOpts = [];
    try {
        const rolesResp = await fetch('/api/v1/users?per_page=1', {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}});
    } catch(e) {}
    // Use the same roles from users config
    rolesOpts = PAGES['users']?.filters?.[0]?.options || [];

    tab0.innerHTML = `
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">NIK <span class="text-danger">*</span></label>
                <input type="text" name="nik" class="form-control" value="${data?.nik||''}" required maxlength="16" pattern="[0-9]{16}" title="NIK harus 16 digit angka" ${isRO?'disabled':''}>
                <small class="text-muted">16 digit angka</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="nama" class="form-control" value="${data?.nama||''}" required ${isRO?'disabled':''}>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">NIP <small class="text-muted">(Opsional)</small></label>
                <input type="text" name="nip" class="form-control" value="${data?.nip||''}" maxlength="18" ${isRO?'disabled':''}>
                <small class="text-muted">Maks 18 karakter</small>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Jabatan <span class="text-danger">*</span></label>
                <input type="text" name="jabatan" class="form-control" value="${data?.jabatan||''}" required ${isRO?'disabled':''}>
            </div>
            <div class="col-md-12"><hr></div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Role <span class="text-danger">*</span></label>
                <select name="role_id" id="opRole" class="form-select" required ${isRO?'disabled':''}>
                    <option value="">-- Pilih Role --</option>
                    ${rolesOpts.map(r=>`<option value="" data-role-name="${r.v}" ${data?.role?.name===r.v?'selected':''}>${r.l}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Skala <span class="text-danger">*</span></label>
                <select name="skala_id" id="opSkala" class="form-select" required ${isRO?'disabled':''}>
                    <option value="">-- Pilih Skala --</option>
                    ${skalaOpts.map(s=>`<option value="${s.id}" ${data?.skala_id===s.id?'selected':''}>${s.nama}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Cabor <small class="text-muted">(Opsional)</small></label>
                <select name="cabor_id" class="form-select" ${isRO?'disabled':''}>
                    <option value="">-- Tidak Ada --</option>
                    ${caborOpts.map(c=>`<option value="${c.id}" ${data?.cabor_id===c.id?'selected':''}>${c.nama}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-6" id="wrapOpKabKota" style="display:none;">
                <label class="form-label fw-bold text-success">Kab/Kota <span class="text-danger">*</span></label>
                <select name="kabkota_id" id="opKabKota" class="form-select" ${isRO?'disabled':''}>
                    <option value="">-- Pilih Kab/Kota --</option>
                    ${kabOpts.map(k=>`<option value="${k.id}" ${data?.kabkota_id===k.id?'selected':''}>${k.name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-12"><hr></div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Email <small class="text-muted">(Opsional)</small></label>
                <input type="email" name="email" class="form-control" value="${data?.email||''}" ${isRO?'disabled':''}>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">No. Telepon <small class="text-muted">(Opsional)</small></label>
                <input type="text" name="no_telp" class="form-control" value="${data?.no_telp||''}" maxlength="20" ${isRO?'disabled':''}>
            </div>
        </div>
    `;

    // Fetch role IDs from API (Spatie roles table)
    try {
        const resp = await fetch('/api/v1/peran?all', {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}});
    } catch(e) {}

    // Set role_id values by fetching roles from roles table
    const roleSelect = document.getElementById('opRole');
    if (roleSelect) {
        try {
            // Fetch Spatie roles
            const rr = await fetch('/api/v1/public/peran', {headers:{'Accept':'application/json'}}).catch(()=>null);
        } catch(e) {}
        // We need to populate role_id correctly — use data-attribute approach
        // Fetch roles table to get IDs
        // Since roles table is from Spatie, we'll query users endpoint or rely on known IDs
        // Actually, just set value from data.role_id for options
        roleSelect.querySelectorAll('option[data-role-name]').forEach(async (opt, idx) => {
            // For now, set the value based on index — we'll fix this with actual role IDs
        });
    }

    // Actually, let's fetch the Spatie roles to populate IDs correctly
    try {
        const rolesResp = await fetch('/api/v1/operators?per_page=1', {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}});
    } catch(e) {}
    
    // Simple approach: Load roles from a dedicated endpoint or use Spatie role names
    // We'll use a workaround: fetch all roles via the existing API structure
    // The role_id in operators references roles.id from Spatie
    // Let's populate via a simple fetch
    try {
        const rolesRaw = await fetch('/api/v1/skala', {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}});
        // Actually, we need the roles table data. Let's add inline from existing PAGES config
    } catch(e) {}

    // Populate role_id select with actual role IDs from Spatie by fetching roles from DB
    // We'll enhance this: build role options with ID
    if (roleSelect) {
        try {
            const rrr = await fetch('/api/v1/roles', {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}}).catch(()=>null);
            if (rrr && rrr.ok) {
                const rolesData = await rrr.json();
                const allRoles = rolesData.data || rolesData;
                roleSelect.innerHTML = '<option value="">-- Pilih Role --</option>' +
                    allRoles.map(r => `<option value="${r.id}" ${data?.role_id===r.id?'selected':''}>${r.name}</option>`).join('');
            }
        } catch(e) {
            // Fallback: use role name options with manual mapping
        }
    }

    // Conditional Kab/Kota based on Skala
    const skalaEl = document.getElementById('opSkala');
    const wrapOpKab = document.getElementById('wrapOpKabKota');
    function toggleOpKabKota() {
        const selectedSkala = skalaEl?.options[skalaEl.selectedIndex]?.text || '';
        const isDaerah = selectedSkala.toLowerCase() === 'daerah';
        if (wrapOpKab) wrapOpKab.style.display = isDaerah ? 'block' : 'none';
    }
    if (skalaEl) skalaEl.addEventListener('change', toggleOpKabKota);
    toggleOpKabKota();
}

async function submitMultiTabForm() {
    const formEl = document.getElementById('crudForm');

    if (pageSlug === 'orang') {
        const formData = new FormData();
        ['nik','nama','tgl_lahir','gender','gol_darah','tinggi','berat','disabilitas','jenis_disabilitas','telp','alamat','domisili_id','is_active']
            .forEach(k=>{ const el=formEl.querySelector(`[name="${k}"]`); if(el && el.value) formData.append(k, el.value); });
            
        const fotoEl = formEl.querySelector('[name="foto"]');
        if (fotoEl && fotoEl.files.length > 0) formData.append('foto', fotoEl.files[0]);

        const statusRows = document.querySelectorAll('[id^="statusRow_"]');
        const statusItems = [];
        statusRows.forEach(row => {
            const rowIdx = row.dataset.idx;
            const jenisId = row.querySelector(`[name="s_jenis_${rowIdx}"]`)?.value;
            const peranId = row.querySelector(`[name="s_peran_${rowIdx}"]`)?.value;
            const id = row.querySelector(`[name="status_id_${rowIdx}"]`)?.value;
            if (!jenisId || !peranId) return;
            
            statusItems.push({
                jenis_id:   jenisId,
                peran_id:   peranId,
                cabor_id:   row.querySelector(`[name="s_cabor_${rowIdx}"]`)?.value || null,
                skala_id:   row.querySelector(`[name="s_skala_${rowIdx}"]`)?.value || null,
                id_sitenor: row.querySelector(`[name="s_sitenor_${rowIdx}"]`)?.value || null,
                sertifikat_profesi: row.querySelector(`[name="s_sertifikat_${rowIdx}"]`)?.value || null,
                is_active: true,
                id: id ? parseInt(id) : null
            });
        });
        
        // Append status items as JSON string to the FormData so the API can parse it easily, 
        // OR append them as arrays. Let's append as array indices:
        statusItems.forEach((item, i) => {
            Object.keys(item).forEach(k => {
                if(item[k] !== null) formData.append(`status_list[${i}][${k}]`, item[k]);
            });
        });

        if (editingId) formData.append('_method', 'PUT');

        const api = '/api/v1/orang';
        const r = await fetch(editingId?`${api}/${editingId}`:api, {
            method: 'POST',
            headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: formData
        });
        
        if (!r.ok) { const e=await r.json(); Swal.fire({icon:'error',title:'Gagal',html:e.errors?Object.values(e.errors).flat().join('<br>'):e.message}); return; }
        const saved = await r.json();

        bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
        toast('success', editingId?'Data orang diperbarui':'Orang berhasil ditambahkan');
        reloadTable();

    } else if (pageSlug === 'prasarana') {
        const formData = new FormData(formEl);
        const data = {};
        ['nama','lokasi_id','alamat','pengelola','kapasitas','narahubung','telp_narahubung','keterangan','latitude','longitude','jenis_id','kategori','standar']
            .forEach(k=>{ const v=formData.get(k); if(v!==null) data[k]=v; });
        data.cabor_ids = formData.getAll('cabor_ids[]').map(Number).filter(Boolean);

        // Fasilitas
        const fasRows = document.querySelectorAll('[id^="fasRow_"]');
        const fasilitas = [];
        fasRows.forEach(row=>{
            const idx=row.id.split('_')[1];
            const nama=row.querySelector(`[name="fas_nama_${idx}"]`)?.value;
            if(!nama) return;
            fasilitas.push({
                id:       row.querySelector(`[name="fas_id_${idx}"]`)?.value||null,
                nama,
                jumlah:   parseInt(row.querySelector(`[name="fas_jumlah_${idx}"]`)?.value)||1,
                kondisi:  row.querySelector(`[name="fas_kondisi_${idx}"]`)?.value||'Baik',
                keterangan:row.querySelector(`[name="fas_keterangan_${idx}"]`)?.value||null,
            });
        });
        data.fasilitas = fasilitas;

        const api='/api/v1/prasarana';
        const r=await fetch(editingId?`${api}/${editingId}`:api,{
            method:editingId?'PUT':'POST',
            headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json','Accept':'application/json'},
            body:JSON.stringify(data)
        });

        if (r.ok) {
            const saved = await r.json();
            // Upload foto jika ada
            const fotoFiles = document.getElementById('fotoUpload')?.files;
            if (fotoFiles?.length) {
                const fd=new FormData();
                Array.from(fotoFiles).forEach(f=>fd.append('fotos[]',f));
                await fetch(`/api/v1/prasarana/${saved.id}/foto`,{method:'POST',headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},body:fd});
            }
            bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
            toast('success',editingId?'Prasarana diperbarui':'Prasarana berhasil ditambahkan');
            reloadTable();
        } else {
            const e=await r.json();
            Swal.fire({icon:'error',title:'Gagal',html:e.errors?Object.values(e.errors).flat().join('<br>'):e.message});
        }

    } else if (pageSlug === 'events') {
        const formData = new FormData(formEl);
        const data = {};
        ['kab_kota_id','nama','jenis_id','jenis_event','skala_id','penyelenggara','lokasi_kegiatan','tanggal_mulai','tanggal_selesai','status','disabilitas']
            .forEach(k=>{ const v=formData.get(k); if(v) data[k]=v; });
        data.cabor_ids = formData.getAll('cabor_ids[]').map(Number).filter(Boolean);

        const api='/api/v1/events';
        const r=await fetch(editingId?`${api}/${editingId}`:api,{
            method:editingId?'PUT':'POST',
            headers:{'X-CSRF-TOKEN':csrfToken,'Content-Type':'application/json','Accept':'application/json'},
            body:JSON.stringify(data)
        });
        if (r.ok) {
            bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
            toast('success',editingId?'Event diperbarui':'Event berhasil dibuat');
            reloadTable();
        } else {
            const e=await r.json();
            Swal.fire({icon:'error',title:'Gagal',html:e.errors?Object.values(e.errors).flat().join('<br>'):e.message});
        }
    } else if (pageSlug === 'sarana') {
        const formData = new FormData(formEl);
        if (editingId) formData.append('_method', 'PUT');

        const api = '/api/v1/sarana';
        const url = editingId ? `${api}/${editingId}` : api;

        const r = await fetch(url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
            body: formData
        });

        if (r.ok) {
            bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
            toast('success', editingId ? 'Sarana diperbarui' : 'Sarana berhasil ditambahkan');
            reloadTable();
        } else {
            const e = await r.json();
            Swal.fire({icon: 'error',title: 'Gagal Menyimpan',html: e.errors ? Object.values(e.errors).flat().join('<br>') : (e.message || 'Gagal menyimpan')});
        }
    } else if (pageSlug === 'organisasi') {
        const formData = new FormData(formEl);
        if (editingId) formData.append('_method', 'PUT');

        const api = '/api/v1/organisasi';
        const url = editingId ? `${api}/${editingId}` : api;

        const r = await fetch(url, {
            method: 'POST',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
            body: formData
        });

        if (r.ok) {
            bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
            toast('success', editingId ? 'Organisasi diperbarui' : 'Organisasi berhasil ditambahkan');
            reloadTable();
        } else {
            const e = await r.json();
            Swal.fire({icon: 'error',title: 'Gagal Menyimpan',html: e.errors ? Object.values(e.errors).flat().join('<br>') : (e.message || 'Gagal menyimpan')});
        }
    } else if (pageSlug === 'users') {
        const data = Object.fromEntries(new FormData(formEl));
        if (editingId && !data.password) delete data.password;
        
        // Bersihkan data yang disembunyikan
        const role = data.role || '';
        if (!role.includes('Kab/Kota') && !role.includes('Kwarcab') && !role.includes('Pengcab')) delete data.kab_kota_id;
        if (!role.includes('Koni') && !role.includes('Kormi') && !role.includes('NPCI') && !role.includes('Dispora') && !role.includes('Inorga') && !role.includes('Cabor') && !role.includes('Pengprov') && !role.includes('Pengcab')) delete data.jenis_id;
        
        // Hapus cabor jika jenis sistem bukan Prestasi (1) atau Masyarakat (2)
        if (data.jenis_id != 1 && data.jenis_id != 2) delete data.cabor_id;

        const api = '/api/v1/users';
        const url = editingId ? `${api}/${editingId}` : api;
        
        const r = await fetch(url, {
            method: editingId ? 'PUT' : 'POST',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify(data)
        });

        if (r.ok) {
            bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
            toast('success', editingId ? 'User diperbarui' : 'User berhasil ditambahkan');
            reloadTable();
        } else {
            const e = await r.json();
            Swal.fire({icon: 'error',title: 'Gagal Menyimpan',html: e.errors ? Object.values(e.errors).flat().join('<br>') : (e.message || 'Gagal menyimpan')});
        }
    } else if (pageSlug === 'operators') {
        const data = Object.fromEntries(new FormData(formEl));

        // Clean empty optional fields
        if (!data.cabor_id) delete data.cabor_id;
        if (!data.user_id) delete data.user_id;
        
        // Check skala — if not Daerah, remove kabkota_id
        const skalaEl = document.getElementById('opSkala');
        const selectedSkala = skalaEl?.options[skalaEl.selectedIndex]?.text || '';
        if (selectedSkala.toLowerCase() !== 'daerah') delete data.kabkota_id;

        const api = '/api/v1/operators';
        const url = editingId ? `${api}/${editingId}` : api;
        
        const r = await fetch(url, {
            method: editingId ? 'PUT' : 'POST',
            headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
            body: JSON.stringify(data)
        });

        if (r.ok) {
            bootstrap.Modal.getInstance(document.getElementById('formModal'))?.hide();
            toast('success', editingId ? 'Operator diperbarui' : 'Operator berhasil ditambahkan');
            reloadTable();
        } else {
            const e = await r.json();
            Swal.fire({icon: 'error',title: 'Gagal Menyimpan',html: e.errors ? Object.values(e.errors).flat().join('<br>') : (e.message || 'Gagal menyimpan')});
        }
    }
}

// ═══ VIEW ROW (read-only detail) ═════════════════════════
async function viewRow(id) {
    const cfg = PAGES[pageSlug];
    try {
        const r = await fetch(`${cfg.api}/${id}`, {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrfToken}});
        if (!r.ok) throw new Error('Gagal memuat data');
        const data = await r.json();
        editingId = null;
        _isViewMode = true;
        const header = document.querySelector('#formModal .modal-header');
        header.className = 'modal-header text-white bg-info bg-gradient';
        
        // Custom View for Orang, Prasarana, & Sarana
        if (pageSlug === 'orang') {
            await buildOrangReportView(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (pageSlug === 'prasarana') {
            await buildPrasaranaReportView(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (pageSlug === 'sarana') {
            await buildSaranaReportView(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (pageSlug === 'users') {
            await buildUserTabs(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (pageSlug === 'operators') {
            await buildOperatorTabs(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (pageSlug === 'organisasi') {
            await buildOrganisasiReportView(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (pageSlug === 'events') {
            await buildEventReportView(data);
            new bootstrap.Modal(document.getElementById('formModal')).show();
        } else if (cfg.multiTab) {
            await editRow(id);
        } else {
            editingId = id;
            await editRow(id);
        }
        setTimeout(() => {
            document.querySelectorAll('#formFields input, #formFields select, #formFields textarea').forEach(el => el.disabled = true);
            document.getElementById('btnSave').style.display = 'none';
            document.getElementById('formModalTitle').innerHTML = '<i class="bi bi-eye me-2"></i>Detail Data';
            
            // Hide action buttons in view mode
            document.querySelectorAll('#formFields .btn-outline-primary, #formFields .btn-outline-success, #formFields .btn-outline-warning, #formFields .btn-action.danger, #formFields .remove-row').forEach(el => el.style.display = 'none');
            
            // Hide file upload forms
            document.querySelectorAll('#formFields input[type="file"]').forEach(el => {
                const parent = el.closest('.mb-3');
                if (parent) parent.style.display = 'none';
                else el.style.display = 'none';
            });
            
            _isViewMode = false;
        }, 300);
    } catch(e) {
        toast('error', e.message);
    }
}

// ═══ IMPORT MODAL ════════════════════════════════════════
function openImportModal() {
    const cfg = PAGES[pageSlug];
    // Reset modal steps
    document.getElementById('importStep1').style.display = '';
    document.getElementById('importStep2').style.display = 'none';
    document.getElementById('importStep3').style.display = 'none';
    document.getElementById('btnDoImport').style.display = '';
    document.getElementById('importFile').value = '';

    // Build reference data buttons
    const refGroup = document.getElementById('importRefBtnGroup');
    const refContainer = document.getElementById('importRefButtons');
    refGroup.innerHTML = '';
    if (cfg.refData && cfg.refData.length) {
        refContainer.style.display = '';
        cfg.refData.forEach(ref => {
            refGroup.insertAdjacentHTML('beforeend',
                `<button type="button" class="btn btn-outline-dark btn-sm" onclick="showRefData('${ref.label}','${ref.cache}','${ref.nameField}')">
                    <i class="bi bi-table me-1"></i>${ref.label}
                </button>`);
        });
    } else {
        refContainer.style.display = 'none';
    }

    new bootstrap.Modal(document.getElementById('importModal')).show();
}

function downloadTemplate() {
    const typeMap = {
        orang:'orang', prasarana:'prasarana', events:'events', organisasi:'organisasi', sarana:'sarana',
        sekolah:'sekolah', ekstrakurikuler:'ekstrakurikuler', 'master-jenis-ekstrakurikuler':'master-jenis-ekstrakurikuler'
    };
    const type = typeMap[pageSlug];
    if (!type) { toast('error','Template tidak tersedia'); return; }
    window.location.href = `/api/v1/template/${type}`;
}

function showRefData(label, cacheName, nameField) {
    const cache = window[cacheName];
    if (!cache || !cache.length) { toast('info','Data referensi belum dimuat'); return; }
    document.getElementById('refDataTitle').innerHTML = `<i class="bi bi-book me-2"></i>Referensi: ${label}`;
    const tbody = document.getElementById('refDataBody');
    tbody.innerHTML = cache.map(item =>
        `<tr><td>${item.id}</td><td>${item[nameField] || item.nama || item.name || '-'}</td></tr>`
    ).join('');
    new bootstrap.Modal(document.getElementById('refDataModal')).show();
}

async function doImport() {
    const file = document.getElementById('importFile').files[0];
    if (!file) { toast('error','Pilih file Excel terlebih dahulu'); return; }

    const typeMap = {
        orang:'orang', prasarana:'prasarana', events:'events', organisasi:'organisasi', sarana:'sarana',
        sekolah:'sekolah', ekstrakurikuler:'ekstrakurikuler', 'master-jenis-ekstrakurikuler':'master-jenis-ekstrakurikuler'
    };
    const type = typeMap[pageSlug];
    if (!type) { toast('error','Import tidak tersedia untuk halaman ini'); return; }

    // Show progress
    document.getElementById('importStep1').style.display = 'none';
    document.getElementById('importStep2').style.display = '';
    document.getElementById('btnDoImport').style.display = 'none';
    const progressBar = document.getElementById('importProgressBar');
    const progressText = document.getElementById('importProgressText');
    progressBar.style.width = '30%';
    progressText.textContent = 'Mengupload file...';

    const fd = new FormData();
    fd.append('file', file);

    try {
        progressBar.style.width = '60%';
        progressText.textContent = 'Memproses data...';

        const r = await fetch(`/api/v1/import/${type}`, {
            method:'POST', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body:fd
        });
        const j = await r.json();

        progressBar.style.width = '100%';

        // Show results
        document.getElementById('importStep2').style.display = 'none';
        document.getElementById('importStep3').style.display = '';

        const summary = document.getElementById('importResultSummary');
        const detail = document.getElementById('importResultDetail');

        if (r.ok) {
            summary.innerHTML = `
                <div class="alert alert-${j.gagal > 0 ? 'warning' : 'success'} py-3">
                    <h6 class="mb-2"><i class="bi bi-check-circle me-1"></i>Import Selesai!</h6>
                    <div class="d-flex gap-4">
                        <div><strong class="fs-4 text-success">${j.berhasil}</strong><br><small>Berhasil</small></div>
                        <div><strong class="fs-4 text-danger">${j.gagal}</strong><br><small>Gagal</small></div>
                        <div><strong class="fs-4 text-primary">${j.total}</strong><br><small>Total</small></div>
                    </div>
                </div>`;

            if (j.detail_gagal && j.detail_gagal.length) {
                detail.innerHTML = `
                    <div class="alert alert-danger py-2 mb-2"><strong><i class="bi bi-exclamation-triangle me-1"></i>Detail Data Gagal</strong></div>
                    <table class="table table-sm table-bordered">
                        <thead class="table-danger"><tr><th style="width:50px">No</th><th style="width:60px">Baris</th><th>Data</th><th>Alasan</th></tr></thead>
                        <tbody>${j.detail_gagal.map((g,i) =>
                            `<tr><td>${i+1}</td><td>${g.baris}</td><td class="small">${Object.entries(g.data||{}).map(([k,v])=>`${k}: ${v||'-'}`).join(', ')}</td><td class="text-danger small">${g.alasan}</td></tr>`
                        ).join('')}</tbody>
                    </table>`;
            }
            reloadTable();
        } else {
            summary.innerHTML = `<div class="alert alert-danger py-3"><h6><i class="bi bi-x-circle me-1"></i>Error</h6>${j.message||'Terjadi kesalahan'}</div>`;
        }
    } catch(e) {
        document.getElementById('importStep2').style.display = 'none';
        document.getElementById('importStep3').style.display = '';
        document.getElementById('importResultSummary').innerHTML = `<div class="alert alert-danger">Error: ${e.message}</div>`;
    }
}

// ═══ EXPORT MODAL ════════════════════════════════════════
function openExportModal() {
    const cfg = PAGES[pageSlug];
    const container = document.getElementById('exportFilterFields');
    container.innerHTML = '';

    // Build export filter fields from page's filter config
    if (cfg.filters && cfg.filters.length) {
        cfg.filters.forEach(f => {
            let options = '';
            if (f.options) {
                options = f.options.map(o => `<option value="${o.v}">${o.l}</option>`).join('');
            } else if (f.cache && window[f.cache]) {
                options = window[f.cache].map(item =>
                    `<option value="${item[f.optionKey||'id']}">${item[f.optionLabel||'nama']}</option>`
                ).join('');
            }
            container.insertAdjacentHTML('beforeend', `
                <div class="col-md-6">
                    <label class="form-label small fw-semibold">${f.label}</label>
                    <select class="form-select form-select-sm" id="export_${f.name}">
                        <option value="">— Semua —</option>${options}
                    </select>
                </div>`);
        });
    } else {
        container.innerHTML = '<p class="text-muted small">Tidak ada filter. Semua data akan di-export.</p>';
    }

    new bootstrap.Modal(document.getElementById('exportModal')).show();
}

function doExport() {
    const cfg = PAGES[pageSlug];
    const typeMap = {
        orang:'orang', prasarana:'prasarana', events:'events', organisasi:'organisasi',
        informasi:'informasi', pengumuman:'pengumuman', users:'users', 'log-sistem':'log-sistem'
    };
    const type = typeMap[pageSlug];
    if (!type) { toast('error','Export tidak tersedia'); return; }

    const params = new URLSearchParams();
    if (cfg.filters) {
        cfg.filters.forEach(f => {
            const el = document.getElementById(`export_${f.name}`);
            if (el && el.value) params.set(f.name, el.value);
        });
    }

    window.location.href = `/api/v1/export/${type}?${params}`;
    bootstrap.Modal.getInstance(document.getElementById('exportModal'))?.hide();
    toast('success', 'Download dimulai...');
}

// ═══ CUSTOM VIEW UNTUK DATA ORANG (CV STYLE) ══════════════
async function buildOrangReportView(data) {
    document.getElementById('modalTabs').style.setProperty('display','none','important');
    const container = document.getElementById('formFields');
    container.className = "modal-body py-4 bg-light";
    document.getElementById('btnSave').style.display = 'none';

    // Data List
    const statusList = data.status_list || data.statusList || [];
    const riwayatList = data.riwayat_event || data.riwayatEvent || [];

    // Tentukan badge status aktif
    const badgeAktif = data.is_active 
        ? '<span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>'
        : '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger"><i class="bi bi-x-circle me-1"></i>Non-Aktif</span>';

    // Format Tanggal
    let tglLahirStr = '-';
    if (data.tgl_lahir) {
        const d = new Date(data.tgl_lahir);
        tglLahirStr = d.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
    }

    let html = `
    <!-- HEADER PROFIL -->
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 position-relative">
            <div class="position-absolute top-0 end-0 p-3">${badgeAktif}</div>
            <div class="d-flex flex-column flex-md-row align-items-center gap-4">
                <img src="${data.foto ? '/storage/' + data.foto : '/assets/img/default-avatar.png'}" 
                     class="img-thumbnail rounded-circle shadow-sm" 
                     style="width: 130px; height: 130px; object-fit: cover;">
                <div class="text-center text-md-start">
                    <h4 class="fw-bold mb-1">${data.nama || '-'}</h4>
                    <p class="text-muted mb-2"><i class="bi bi-upc-scan me-1"></i> NIK: ${data.nik || '-'}</p>
                    <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                        ${data.sportif_id ? `<span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2"><i class="bi bi-fingerprint me-1"></i>ID: ${data.sportif_id}</span>` : ''}
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2"><i class="bi bi-geo-alt me-1"></i>${data.domisili?.name || '-'}</span>
                        ${data.disabilitas ? `<span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2"><i class="bi bi-person-wheelchair me-1"></i>Difabel: ${(data.jenis_disabilitas||'').replace('_',' ')}</span>` : ''}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DATA PRIBADI -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-person-lines-fill me-2"></i>Informasi Pribadi</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Gender</label>
                    <span class="fw-medium">${data.gender==='L'?'Laki-laki':data.gender==='P'?'Perempuan':'-'}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Tanggal Lahir</label>
                    <span class="fw-medium">${tglLahirStr}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Golongan Darah</label>
                    <span class="fw-medium">${data.gol_darah||'-'}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">No. Telp</label>
                    <span class="fw-medium">${data.telp||'-'}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Tinggi / Berat</label>
                    <span class="fw-medium">${data.tinggi?data.tinggi+' cm':'-'} / ${data.berat?data.berat+' kg':'-'}</span>
                </div>
                <div class="col-12">
                    <label class="small text-muted d-block mb-1">Alamat Domisili</label>
                    <span class="fw-medium">${data.alamat||'-'}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- STATUS OLAHRAGA -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-success"><i class="bi bi-tag-fill me-2"></i>Status Olahraga</h6>
        </div>
        <div class="card-body p-0">
            ${statusList.length ? `
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-4">Domain</th>
                            <th>Peran</th>
                            <th>Cabor / Organisasi</th>
                            <th>Skala / Sertifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${statusList.map(s => `
                        <tr>
                            <td class="ps-4"><span class="fw-medium">${s.jenis?.nama||'-'}</span></td>
                            <td><span class="badge bg-success bg-opacity-10 text-success">${s.peran?.nama||'-'}</span></td>
                            <td>
                                <div>${s.cabor?.nama||'-'}</div>
                                ${s.sitenor_id?`<small class="text-muted">Sitenor: ${s.sitenor_id}</small>`:''}
                            </td>
                            <td>
                                <div>${s.skala?.nama||'-'}</div>
                                ${s.sertifikat_profesi?`<small class="text-muted"><i class="bi bi-award me-1"></i>${s.sertifikat_profesi}</small>`:''}
                            </td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>` : '<div class="p-4 text-center text-muted">Belum ada status olahraga terdaftar.</div>'}
        </div>
    </div>

    <!-- RIWAYAT EVENT -->
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-trophy-fill me-2"></i>Riwayat Event & Prestasi</h6>
        </div>
        <div class="card-body p-0">
            ${riwayatList.length ? `
            <div class="list-group list-group-flush rounded-bottom">
                ${riwayatList.map(r => `
                <div class="list-group-item p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="fw-bold mb-1">${r.event?.nama||'Event Tidak Diketahui'}</h6>
                            <div class="text-muted small mb-2">
                                <i class="bi bi-calendar-event me-1"></i>${r.tanggal ? new Date(r.tanggal).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) : '-'} 
                                <span class="mx-2">•</span> 
                                <i class="bi bi-geo-alt me-1"></i>${r.event?.lokasi_kegiatan||'-'}
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i>${r.cabor?.nama||'-'}</span>
                                ${r.kategori?`<span class="badge bg-light text-dark border">${r.kategori}</span>`:''}
                                ${r.prestasi?`<span class="badge bg-light text-dark border text-capitalize">${r.prestasi}</span>`:''}
                                ${r.medali?`<span class="badge border ${r.medali==='emas'?'bg-warning text-dark border-warning':r.medali==='perak'?'bg-secondary text-white border-secondary':r.medali==='perunggu'?'bg-orange text-white border-orange':'bg-light text-dark'}">${r.medali.toUpperCase()}</span>`:''}
                            </div>
                        </div>
                    </div>
                </div>`).join('')}
            </div>` : '<div class="p-4 text-center text-muted">Belum ada riwayat event.</div>'}
        </div>
    </div>
    `;

    container.innerHTML = html;
}

// ═══ CUSTOM VIEW UNTUK DATA PRASARANA (PROFIL LENGKAP) ══════════════
async function buildPrasaranaReportView(data) {
    document.getElementById('modalTabs').style.setProperty('display','none','important');
    const container = document.getElementById('formFields');
    container.className = "modal-body py-4 bg-light";
    document.getElementById('btnSave').style.display = 'none';

    const fotos = data.fotos || [];
    const fasilitas = data.fasilitas || [];
    const cabors = data.cabors || [];

    // Galeri Foto
    let galleryHtml = '';
    if (fotos.length > 0) {
        galleryHtml = `
        <div class="row g-2 mb-4">
            ${fotos.slice(0,4).map((f, i) => `
                <div class="${fotos.length===1 ? 'col-12' : (i===0 ? 'col-md-8' : 'col-md-4')}">
                    <img src="/storage/${f.foto}" class="w-100 rounded shadow-sm" style="height:${fotos.length===1?'300px':'200px'}; object-fit:cover;">
                </div>
            `).join('')}
        </div>`;
    }

    // Google Maps Button Location
    let gmapBtn = '';
    const hasCoords = data.latitude && data.longitude;
    const gmapUrl = hasCoords ? `https://www.google.com/maps?q=${data.latitude},${data.longitude}` : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(data.nama + ' ' + (data.lokasi?.name || data.alamat || ''))}`;
    
    gmapBtn = `
    <div class="mt-2 mt-md-0 ms-md-auto">
        <a href="${gmapUrl}" target="_blank" class="btn ${hasCoords ? 'btn-success text-white shadow-sm' : 'btn-secondary text-white shadow-sm'}">
            <i class="bi bi-geo-alt me-1"></i> Buka di Google Maps
        </a>
    </div>`;

    let html = `
    ${galleryHtml}

    <!-- HEADER PRASARANA -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 text-center text-md-start">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                <div>
                    <h3 class="fw-bold mb-2">${data.nama || '-'}</h3>
                    <div class="text-muted mb-3"><i class="bi bi-geo-fill me-1"></i>${data.lokasi?.name || data.alamat || 'Lokasi tidak diketahui'}</div>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2"><i class="bi bi-diagram-3 me-1"></i>${data.jenis?.nama || '-'}</span>
                        ${data.kategori ? `<span class="badge bg-info bg-opacity-10 text-info border border-info px-3 py-2"><i class="bi bi-tags me-1"></i>${data.kategori}</span>` : ''}
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2"><i class="bi bi-star me-1"></i>Standar: ${data.standar || 'Belum di Standarisasi'}</span>
                    </div>
                </div>
                ${gmapBtn}
            </div>
        </div>
    </div>

    <!-- DETAIL INFORMASI -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-info-square me-2"></i>Informasi Detail</h6>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Pengelola</label>
                    <span class="fw-medium">${data.pengelola||'-'}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Kapasitas Penonton</label>
                    <span class="fw-medium">${data.kapasitas ? data.kapasitas.toLocaleString('id-ID') : '-'}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Narahubung</label>
                    <span class="fw-medium">${data.narahubung||'-'}</span>
                </div>
                <div class="col-sm-6 col-md-3">
                    <label class="small text-muted d-block mb-1">Telp Narahubung</label>
                    <span class="fw-medium">${data.telp_narahubung||'-'}</span>
                </div>
                <div class="col-12">
                    <label class="small text-muted d-block mb-1">Alamat Lengkap</label>
                    <span class="fw-medium">${data.alamat||'-'}</span>
                </div>
                ${data.keterangan ? `
                <div class="col-12">
                    <label class="small text-muted d-block mb-1">Keterangan Tambahan</label>
                    <span class="fw-medium">${data.keterangan}</span>
                </div>` : ''}
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- CABANG OLAHRAGA -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-success"><i class="bi bi-dribbble me-2"></i>Cabang Olahraga</h6>
                </div>
                <div class="card-body">
                    ${cabors.length ? `
                    <div class="d-flex flex-wrap gap-2">
                        ${cabors.map(c => `<span class="badge bg-light text-dark border"><i class="bi bi-check-lg text-success me-1"></i>${c.nama}</span>`).join('')}
                    </div>` : '<p class="text-muted small text-center my-3">Tidak ada data cabor.</p>'}
                </div>
            </div>
        </div>
        
        <!-- FASILITAS -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-warning"><i class="bi bi-list-check me-2"></i>Daftar Fasilitas</h6>
                </div>
                <div class="card-body p-0">
                    ${fasilitas.length ? `
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light small text-muted">
                                <tr>
                                    <th class="ps-4">Nama Fasilitas</th>
                                    <th>Jml</th>
                                    <th>Kondisi</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${fasilitas.map(f => `
                                <tr>
                                    <td class="ps-4 fw-medium">${f.nama||'-'}</td>
                                    <td>${f.jumlah||'-'}</td>
                                    <td>
                                        <span class="badge ${f.kondisi==='Baik'?'bg-success':f.kondisi==='Rusak Ringan'?'bg-warning text-dark':'bg-danger'} bg-opacity-10 ${f.kondisi==='Baik'?'text-success':f.kondisi==='Rusak Ringan'?'text-warning':'text-danger'}">
                                            ${f.kondisi||'-'}
                                        </span>
                                    </td>
                                    <td class="small text-muted">${f.keterangan||'-'}</td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>` : '<p class="text-muted small text-center my-4">Tidak ada data fasilitas.</p>'}
                </div>
            </div>
        </div>
    </div>
    
    <!-- PETA LOKASI -->
    ${data.latitude && data.longitude ? `
    <div class="card border-0 shadow-sm mb-2">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-map-fill me-2"></i>Peta Lokasi</h6>
            <span class="badge bg-light text-muted border">${data.latitude}, ${data.longitude}</span>
        </div>
        <div class="card-body p-0">
            <div id="viewLeafletMap" style="height:350px; background:#e9ecef;" class="w-100 rounded-bottom"></div>
        </div>
    </div>` : ''}
    `;

    container.innerHTML = html;

    // Inisialisasi Peta Leaflet untuk View (ReadOnly)
    if (data.latitude && data.longitude) {
        setTimeout(() => {
            const mapContainer = document.getElementById('viewLeafletMap');
            if(mapContainer) {
                const map = L.map(mapContainer, {
                    center: [data.latitude, data.longitude],
                    zoom: 15,
                    dragging: false,
                    scrollWheelZoom: false,
                    zoomControl: false
                });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                L.marker([data.latitude, data.longitude]).addTo(map)
                 .bindPopup(`<b>${data.nama}</b><br>${data.alamat||''}`).openPopup();
            }
        }, 300); // Tunggu modal merender DOM
    }
}

// Close autocomplete on outside click
document.addEventListener('click', e => {
    document.querySelectorAll('.autocomplete-results').forEach(el => {
        if (!el.contains(e.target)) el.style.display='none';
    });
});

// ═══ CUSTOM TABS UNTUK ORGANISASI ════════════════════════════════
async function buildOrganisasiTabs(data) {
    const isReadOnly = _isViewMode;
    const hideWriteUI = isReadOnly;
    
    // Cache Options
    const jenisOpts = (_jenisCache||[]).map(j=>`<option value="${j.id}" ${data?.jenis_id==j.id?'selected':''}>${j.nama}</option>`).join('');
    const skalaOpts = [
        {id:1,nama:'Daerah'},{id:2,nama:'Provinsi'},{id:3,nama:'Nasional'},{id:4,nama:'Internasional'}
    ].map(s=>`<option value="${s.id}" ${data?.skala_id==s.id?'selected':''}>${s.nama}</option>`).join('');
    
    let kabKotaOpts = '';
    if(window._kabKotaCache) {
        kabKotaOpts = window._kabKotaCache.map(k=>`<option value="${k.id}" ${data?.kab_kota_id==k.id?'selected':''}>${k.name}</option>`).join('');
    }

    // Tab 0: Info Dasar
    document.getElementById('tabSection_0').innerHTML = `
    <div class="row g-4">
        <div class="col-md-8">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-semibold">Nama Organisasi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama" value="${data?.nama||''}" required ${isReadOnly?'readonly':''}>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Jenis Organisasi <span class="text-danger">*</span></label>
                    <select class="form-select" name="jenis_id" required ${isReadOnly?'disabled':''}>
                        <option value="">— Pilih Jenis —</option>
                        ${jenisOpts}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Skala Organisasi</label>
                    <select class="form-select" name="skala_id" ${isReadOnly?'disabled':''}>
                        <option value="">— Pilih Skala —</option>
                        ${skalaOpts}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kabupaten/Kota</label>
                    <select class="form-select" name="kab_kota_id" ${isReadOnly?'disabled':''}>
                        <option value="">— Pilih Kab/Kota —</option>
                        ${kabKotaOpts}
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status Operasional</label>
                    <select class="form-select" name="status" ${isReadOnly?'disabled':''}>
                        <option value="Aktif" ${data?.status==='Aktif'?'selected':''}>Aktif</option>
                        <option value="Non-Aktif" ${data?.status==='Non-Aktif'?'selected':''}>Non-Aktif</option>
                        <option value="Dalam Pengawasan" ${data?.status==='Dalam Pengawasan'?'selected':''}>Dalam Pengawasan</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border border-light shadow-sm">
                <div class="card-header bg-light py-2">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-image me-2"></i>Logo Organisasi</h6>
                </div>
                <div class="card-body text-center">
                    <img id="organisasiLogoPreview" src="${data?.logo ? '/storage/'+data.logo : '/assets/img/default-org.png'}" class="img-fluid rounded mb-3" style="max-height: 180px; object-fit: contain;">
                    ${!hideWriteUI ? `
                    <input type="file" name="logo" id="organisasiLogoInput" class="form-control form-control-sm" accept="image/*" onchange="previewOrganisasiLogo(this)">
                    <small class="text-muted d-block mt-2">Format: JPG, PNG, WEBP (Max 2MB)</small>
                    ` : ''}
                </div>
            </div>
        </div>
    </div>`;

    // Tab 1: Info Tambahan
    document.getElementById('tabSection_1').innerHTML = `
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Alamat Lengkap</label>
            <textarea class="form-control" name="alamat" rows="2" ${isReadOnly?'readonly':''}>${data?.alamat||''}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Email Organisasi</label>
            <input type="email" class="form-control" name="email" value="${data?.email||''}" ${isReadOnly?'readonly':''}>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Telepon Kantor</label>
            <input type="text" class="form-control" name="telp" value="${data?.telp||''}" ${isReadOnly?'readonly':''}>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Narahubung (CP)</label>
            <input type="text" class="form-control" name="narahubung" value="${data?.narahubung||''}" ${isReadOnly?'readonly':''}>
        </div>
        <div class="col-12 mt-4">
            <h6 class="fw-bold border-bottom pb-2"><i class="bi bi-file-earmark-text me-2"></i>Legalitas / SK Pendirian</h6>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Nomor SK Pendirian</label>
            <input type="text" class="form-control" name="sk_pendirian" value="${data?.sk_pendirian||''}" ${isReadOnly?'readonly':''}>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Tanggal SK Pendirian</label>
            <input type="date" class="form-control" name="tgl_sk_pendirian" value="${data?.tgl_sk_pendirian||''}" ${isReadOnly?'readonly':''}>
        </div>
    </div>`;

    // Tab 2: Peta (Leaflet)
    document.getElementById('tabSection_2').innerHTML = `
    <p class="text-muted small">Klik pada peta untuk menentukan koordinat letak kantor/sekretariat organisasi.</p>
    <div class="row g-2 mb-2">
        <div class="col-md-5"><label class="form-label form-label-sm">Latitude</label>
            <input class="form-control form-control-sm" name="latitude" id="latInput" value="${data?.latitude||''}" placeholder="-7.xxxx" ${hideWriteUI?'readonly':''}></div>
        <div class="col-md-5"><label class="form-label form-label-sm">Longitude</label>
            <input class="form-control form-control-sm" name="longitude" id="lngInput" value="${data?.longitude||''}" placeholder="112.xxxx" ${hideWriteUI?'readonly':''}></div>
        ${!hideWriteUI ? `
        <div class="col-md-2 d-flex align-items-end">
            <button type="button" class="btn btn-secondary btn-sm w-100" onclick="updateMapFromInput()">Set Peta</button>
        </div>` : ''}
    </div>
    <div id="mapContainer" style="height:400px; width:100%; border-radius:8px; z-index:1;"></div>
    `;
}

function previewOrganisasiLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('organisasiLogoPreview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}

// ═══ CUSTOM TABS UNTUK SARANA ════════════════════════════════
function buildSaranaTabs(data) {
    const isReadOnly = _isViewMode;
    
    // Tab 1: Info Dasar
    const jenisOpts = (_jenisCache||[]).map(j=>`<option value="${j.id}" ${data?.jenis_id==j.id?'selected':''}>${j.nama}</option>`).join('');
    const caborOpts = (_caborCache||[]).map(c=>`<option value="${c.id}" ${data?.cabor_id==c.id?'selected':''}>${c.nama}</option>`).join('');

    document.getElementById('tabSection_0').innerHTML = `
    <div class="row g-4">
        <div class="col-md-8">
            <label class="form-label fw-semibold"><i class="bi bi-box-seam me-1"></i>Nama Barang <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nama_barang" value="${data?.nama_barang||''}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold"><i class="bi bi-upc-scan me-1"></i>Kode Inventaris</label>
            <input type="text" class="form-control" name="kode_inventaris" value="${data?.kode_inventaris||''}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-diagram-3 me-1"></i>Domain (Jenis)</label>
            <select class="form-select" name="jenis_id">
                <option value="">— Pilih Domain —</option>
                ${jenisOpts}
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-dribbble me-1"></i>Cabor (Opsional)</label>
            <select class="form-select" name="cabor_id">
                <option value="">— Pilih Cabor —</option>
                ${caborOpts}
            </select>
        </div>
    </div>`;

    // Tab 2: Spesifikasi
    document.getElementById('tabSection_1').innerHTML = `
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-123 me-1"></i>Jumlah</label>
            <input type="number" class="form-control" name="jumlah" value="${data?.jumlah||''}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-tag me-1"></i>Satuan (buah, set, dll)</label>
            <input type="text" class="form-control" name="satuan" value="${data?.satuan||''}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-calendar me-1"></i>Tahun Pengadaan</label>
            <input type="number" class="form-control" name="tahun_pengadaan" value="${data?.tahun_pengadaan||''}" placeholder="Contoh: 2024">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-cash-coin me-1"></i>Sumber Dana</label>
            <input type="text" class="form-control" name="sumber_dana" value="${data?.sumber_dana||''}">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="bi bi-card-text me-1"></i>Spesifikasi Lengkap</label>
            <textarea class="form-control" name="spesifikasi" rows="4">${data?.spesifikasi||''}</textarea>
        </div>
    </div>`;

    // Tab 3: Lokasi & Status
    const prasaranaOpts = (_prasaranaCache||[]).map(p=>`<option value="${p.id}" ${data?.lokasi_barang==p.id?'selected':''}>${p.nama}</option>`).join('');
    const kabOpts = (_kabKotaCache||[]).map(k=>`<option value="${k.id}" ${data?.kab_kota_id==k.id?'selected':''}>${k.name}</option>`).join('');

    const posisiAset = data?.posisi_aset || 'internal_dinas';
    const sKondisi = data?.kondisi || 'baik';
    const sStatus = data?.status || 'tersedia';

    document.getElementById('tabSection_2').innerHTML = `
    <div class="row g-4">
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-heart-pulse me-1"></i>Kondisi</label>
            <select class="form-select" name="kondisi">
                <option value="baik" ${sKondisi=='baik'?'selected':''}>🟢 Baik</option>
                <option value="rusak_ringan" ${sKondisi=='rusak_ringan'?'selected':''}>🟡 Rusak Ringan</option>
                <option value="rusak_berat" ${sKondisi=='rusak_berat'?'selected':''}>🔴 Rusak Berat</option>
                <option value="butuh_perbaikan" ${sKondisi=='butuh_perbaikan'?'selected':''}>🛠️ Butuh Perbaikan</option>
                <option value="dalam_perbaikan" ${sKondisi=='dalam_perbaikan'?'selected':''}>🏗️ Dalam Perbaikan</option>
                <option value="tidak_layak" ${sKondisi=='tidak_layak'?'selected':''}>❌ Tidak Layak</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-shield-check me-1"></i>Status</label>
            <select class="form-select" name="status">
                <option value="tersedia" ${sStatus=='tersedia'?'selected':''}>Tersedia</option>
                <option value="dipakai" ${sStatus=='dipakai'?'selected':''}>Dipakai</option>
                <option value="dipinjam" ${sStatus=='dipinjam'?'selected':''}>Dipinjam</option>
                <option value="dipelihara" ${sStatus=='dipelihara'?'selected':''}>Dipelihara</option>
                <option value="hilang" ${sStatus=='hilang'?'selected':''}>Hilang</option>
                <option value="rusak_total" ${sStatus=='rusak_total'?'selected':''}>Rusak Total</option>
                <option value="dijual" ${sStatus=='dijual'?'selected':''}>Dijual</option>
                <option value="dimusnahkan" ${sStatus=='dimusnahkan'?'selected':''}>Dimusnahkan</option>
            </select>
        </div>
        <div class="col-12"><hr class="my-2"></div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-geo-alt me-1"></i>Posisi Aset</label>
            <select class="form-select" name="posisi_aset" id="saranaPosisi" onchange="toggleSaranaLokasi()">
                <option value="internal_dinas" ${posisiAset=='internal_dinas'?'selected':''}>Internal Dinas</option>
                <option value="prasarana" ${posisiAset=='prasarana'?'selected':''}>Prasarana</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold"><i class="bi bi-pin-map me-1"></i>Kab/Kota Asal</label>
            <select class="form-select" name="kab_kota_id">
                <option value="">— Pilih Kab/Kota —</option>
                ${kabOpts}
            </select>
        </div>
        
        <div class="col-12" id="wrapSaranaPrasarana" style="display:${posisiAset=='prasarana'?'block':'none'}">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <label class="form-label fw-semibold"><i class="bi bi-building me-1"></i>Pilih Prasarana</label>
                    <select class="form-select" name="lokasi_barang">
                        <option value="">— Pilih Prasarana —</option>
                        ${prasaranaOpts}
                    </select>
                    <small class="text-muted mt-1 d-block">Tentukan prasarana tempat barang ini berada.</small>
                </div>
            </div>
        </div>
        <div class="col-12" id="wrapSaranaKeterangan" style="display:${posisiAset=='internal_dinas'?'block':'none'}">
            <div class="card bg-light border-0">
                <div class="card-body">
                    <label class="form-label fw-semibold"><i class="bi bi-door-open me-1"></i>Keterangan Ruangan/Gudang</label>
                    <input type="text" class="form-control" name="keterangan_lokasi" value="${data?.keterangan_lokasi||''}" placeholder="Contoh: Gudang Bawah, Ruang Rapat, dll">
                </div>
            </div>
        </div>
    </div>`;

    // Tab 4: Foto
    document.getElementById('tabSection_3').innerHTML = `
    <div class="row g-4">
        <div class="col-12">
            <label class="form-label fw-semibold"><i class="bi bi-cloud-upload me-1"></i>Upload Foto Barang Baru</label>
            <div class="p-4 border border-2 border-dashed rounded text-center" style="background:#f8f9fa;">
                <input type="file" class="form-control form-control-sm mx-auto" name="foto_barang" accept="image/*" style="max-width:300px;">
                <div class="mt-2 text-muted small">Format: JPG, PNG, WEBP (Maks 2MB).<br>Akan otomatis dikonversi ke WebP untuk menghemat penyimpanan.</div>
            </div>
        </div>
        ${data?.foto_barang ? `
        <div class="col-12">
            <label class="form-label fw-semibold text-muted">Foto Saat Ini</label>
            <div>
                <img src="/storage/${data.foto_barang}" class="img-thumbnail shadow-sm rounded" style="max-height: 250px; object-fit: cover;">
            </div>
        </div>
        ` : '<div class="col-12 text-center text-muted small"><i class="bi bi-image" style="font-size:2rem; opacity:0.5;"></i><br>Belum ada foto.</div>'}
    </div>`;
}

function toggleSaranaLokasi() {
    const v = document.getElementById('saranaPosisi').value;
    document.getElementById('wrapSaranaPrasarana').style.display = (v === 'prasarana' ? 'block' : 'none');
    document.getElementById('wrapSaranaKeterangan').style.display = (v === 'internal_dinas' ? 'block' : 'none');
}

// ═══ CUSTOM VIEW UNTUK DATA SARANA (PROFIL LENGKAP) ══════════════
async function buildSaranaReportView(data) {
    document.getElementById('modalTabs').style.setProperty('display','none','important');
    const container = document.getElementById('formFields');
    container.className = "modal-body py-4 bg-light";
    document.getElementById('btnSave').style.display = 'none';

    let photoHtml = '';
    if (data.foto_barang) {
        photoHtml = `
        <div class="mb-4 text-center">
            <img src="/storage/${data.foto_barang}" class="img-fluid rounded shadow-sm w-100" style="max-height: 400px; object-fit: cover;">
        </div>`;
    }

    const kondisiMap = {
        'baik': '<span class="badge bg-success bg-opacity-10 text-success border border-success">🟢 Baik</span>',
        'rusak_ringan': '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning">🟡 Rusak Ringan</span>',
        'rusak_berat': '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger">🔴 Rusak Berat</span>',
        'butuh_perbaikan': '<span class="badge bg-warning bg-opacity-10 text-dark border">🛠️ Butuh Perbaikan</span>',
        'dalam_perbaikan': '<span class="badge bg-info bg-opacity-10 text-info border">🏗️ Dalam Perbaikan</span>',
        'tidak_layak': '<span class="badge bg-dark bg-opacity-10 text-dark border">❌ Tidak Layak</span>'
    };

    const statusMap = {
        'tersedia': 'Tersedia', 'dipakai': 'Dipakai', 'dipinjam': 'Dipinjam',
        'dipelihara': 'Dipelihara', 'hilang': 'Hilang', 'rusak_total': 'Rusak Total',
        'dijual': 'Dijual', 'dimusnahkan': 'Dimusnahkan'
    };

    const lokasiString = data.posisi_aset === 'prasarana' 
        ? `<i class="bi bi-building me-1"></i>Prasarana: <strong>${data.prasarana?.nama || '-'}</strong>` 
        : `<i class="bi bi-door-open me-1"></i>Dinas: <strong>${data.keterangan_lokasi || 'Internal Dinas'}</strong>`;

    let html = `
    ${photoHtml}

    <!-- HEADER SARANA -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-8 text-center text-md-start">
                    <h3 class="fw-bold mb-1">${data.nama_barang || '-'}</h3>
                    <div class="text-muted mb-3"><i class="bi bi-upc-scan me-1"></i>Kode: ${data.kode_inventaris || '-'}</div>
                    <div class="d-flex flex-wrap justify-content-center justify-content-md-start gap-2">
                        ${data.jenis ? `<span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 border"><i class="bi bi-diagram-3 me-1"></i>${data.jenis.nama}</span>` : ''}
                        ${data.cabor ? `<span class="badge bg-info bg-opacity-10 text-info px-3 py-2 border"><i class="bi bi-dribbble me-1"></i>${data.cabor.nama}</span>` : ''}
                    </div>
                </div>
                <div class="col-md-4 text-center text-md-end mt-3 mt-md-0 border-start-md">
                    <div class="d-flex flex-column gap-2 align-items-center align-items-md-end">
                        <div>${kondisiMap[data.kondisi] || '-'}</div>
                        <span class="badge bg-secondary px-3 py-2">Status: ${statusMap[data.status] || data.status || '-'}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- DETAIL SPESIFIKASI -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-list-task me-2"></i>Spesifikasi & Pengadaan</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                            <span class="text-muted small">Jumlah / Satuan</span>
                            <span class="fw-medium">${data.jumlah||'-'} ${data.satuan||''}</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                            <span class="text-muted small">Tahun Pengadaan</span>
                            <span class="fw-medium">${data.tahun_pengadaan||'-'}</span>
                        </li>
                        <li class="list-group-item px-4 py-3 d-flex justify-content-between">
                            <span class="text-muted small">Sumber Dana</span>
                            <span class="fw-medium">${data.sumber_dana||'-'}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- LOKASI -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-geo-alt me-2"></i>Posisi Lokasi Aset</h6>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded border mb-3">
                        ${lokasiString}
                    </div>
                    <div class="text-muted small">
                        <i class="bi bi-pin-map me-1"></i>Wilayah: <strong class="text-dark">${data.kab_kota?.name || '-'}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SPESIFIKASI TEXTAREA -->
    ${data.spesifikasi ? `
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-card-text me-2"></i>Spesifikasi Lengkap</h6>
        </div>
        <div class="card-body">
            <p class="mb-0 text-muted" style="white-space: pre-wrap;">${data.spesifikasi}</p>
        </div>
    </div>` : ''}
    `;

    container.innerHTML = html;
}

// ═══ CUSTOM VIEW UNTUK EVENT ════════════════════════════════
async function buildEventReportView(data) {
    document.getElementById('modalTabs').style.display = 'none';
    const container = document.getElementById('formFields');
    
    // Status Badge Logic
    let statusBadge = '';
    if(data.status === 'aktif') statusBadge = '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-play-circle-fill me-1"></i>Aktif / Berlangsung</span>';
    else if(data.status === 'selesai') statusBadge = '<span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>Selesai</span>';
    else if(data.status === 'dibatalkan') statusBadge = '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-x-circle-fill me-1"></i>Dibatalkan</span>';
    else statusBadge = '<span class="badge bg-dark px-3 py-2 rounded-pill shadow-sm">Belum Ditentukan</span>';

    // Format Dates
    const dtOptions = {day:'numeric', month:'long', year:'numeric'};
    const dateStart = data.tanggal_mulai ? new Date(data.tanggal_mulai).toLocaleDateString('id-ID', dtOptions) : '-';
    const dateEnd = data.tanggal_selesai ? new Date(data.tanggal_selesai).toLocaleDateString('id-ID', dtOptions) : '-';
    
    // Cabors
    const caborsHtml = (data.cabors || []).length > 0 
        ? data.cabors.map(c => `<span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 me-2 mb-2 border border-primary"><i class="bi bi-trophy me-1"></i>${c.nama}</span>`).join('')
        : '<span class="text-muted"><i class="bi bi-exclamation-circle me-1"></i>Belum ada cabor yang dipilih</span>';

    const html = `
    <!-- HEADER -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <div class="card-body p-4 text-white position-relative overflow-hidden">
            <i class="bi bi-calendar2-star text-white position-absolute opacity-25" style="font-size: 8rem; right: -20px; top: -20px; transform: rotate(-15deg);"></i>
            <div class="position-relative z-1">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-white text-primary fw-bold me-2 px-3 py-2 rounded-pill shadow-sm">${(data.jenis_event || 'Event').toUpperCase()}</span>
                    ${statusBadge}
                    ${data.disabilitas ? '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm ms-2"><i class="bi bi-person-wheelchair me-1"></i>Disabilitas</span>' : ''}
                </div>
                <h2 class="fw-bold mb-1">${data.nama}</h2>
                <h6 class="text-white-50 mb-0"><i class="bi bi-building me-1"></i>Penyelenggara: ${data.penyelenggara || '-'}</h6>
            </div>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="row g-4 mb-4">
        <!-- Info Kiri -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle me-2"></i>Informasi Pelaksanaan</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Domain Sistem</div>
                            <div class="fw-medium text-dark"><i class="bi bi-diagram-3 me-2 text-primary"></i>${data.jenis?.nama || '-'}</div>
                        </li>
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Skala Event</div>
                            <div class="fw-medium text-dark"><i class="bi bi-globe me-2 text-primary"></i>${data.skala?.nama || 'Belum Ditentukan'}</div>
                        </li>
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Lokasi Kegiatan</div>
                            <div class="fw-medium text-dark"><i class="bi bi-geo-alt me-2 text-danger"></i>${data.lokasi_kegiatan || '-'}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Info Kanan -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-calendar-range me-2"></i>Jadwal Kegiatan</h6>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="d-flex align-items-center mb-4 bg-light p-3 rounded border">
                        <div class="bg-primary text-white rounded p-3 me-3 text-center" style="width: 70px;">
                            <i class="bi bi-calendar-event fs-3 d-block mb-1"></i>
                            <span class="small fw-bold">Mulai</span>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-dark">${dateStart}</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center bg-light p-3 rounded border">
                        <div class="bg-success text-white rounded p-3 me-3 text-center" style="width: 70px;">
                            <i class="bi bi-calendar-check fs-3 d-block mb-1"></i>
                            <span class="small fw-bold">Selesai</span>
                        </div>
                        <div>
                            <div class="fw-bold fs-5 text-dark">${dateEnd}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CABOR -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-trophy me-2 text-warning"></i>Cabang Olahraga yang Dipertandingkan</h6>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap">
                ${caborsHtml}
            </div>
        </div>
    </div>
    
    <!-- CTA KELOLA PESERTA -->
    <div class="card border-0 shadow-sm bg-success bg-gradient text-white">
        <div class="card-body p-4 text-center">
            <i class="bi bi-people-fill fs-1 d-block mb-2 text-white-50"></i>
            <h4 class="fw-bold mb-2">Manajemen Peserta & Klasemen Medali</h4>
            <p class="mb-3 text-white-50">Event ini telah dialihkan ke halaman manajemen khusus untuk menangani jumlah peserta yang besar secara real-time dan menghitung statistik medali (klasemen) otomatis.</p>
            <a href="/admin/events/${data.id}/peserta" class="btn btn-light btn-lg fw-bold text-success rounded-pill px-5 shadow-sm">
                <i class="bi bi-box-arrow-up-right me-2"></i>Buka Halaman Peserta
            </a>
        </div>
    </div>
    `;

    container.innerHTML = html;
}

// ═══ CUSTOM VIEW UNTUK ORGANISASI ════════════════════════════════
async function buildOrganisasiReportView(data) {
    document.getElementById('modalTabs').style.display = 'none';
    const container = document.getElementById('formFields');
    
    // Status Badge
    let statusBadge = '';
    if(data.status === 'Aktif') statusBadge = '<span class="badge bg-success px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-check-circle-fill me-1"></i>Aktif</span>';
    else if(data.status === 'Non-Aktif') statusBadge = '<span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-x-circle-fill me-1"></i>Non-Aktif</span>';
    else if(data.status === 'Dalam Pengawasan') statusBadge = '<span class="badge bg-warning text-dark px-3 py-2 rounded-pill shadow-sm"><i class="bi bi-exclamation-triangle-fill me-1"></i>Dalam Pengawasan</span>';
    else statusBadge = '<span class="badge bg-secondary px-3 py-2 rounded-pill shadow-sm">Belum Ditentukan</span>';

    // Logo
    const logoSrc = data.logo ? `/storage/${data.logo}` : '/assets/img/default-org.png';
    const dtOptions = {day:'numeric', month:'long', year:'numeric'};
    const tglPendirian = data.tgl_sk_pendirian ? new Date(data.tgl_sk_pendirian).toLocaleDateString('id-ID', dtOptions) : '-';

    const html = `
    <!-- HEADER -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1f4037 0%, #99f2c8 100%);">
        <div class="card-body p-4 position-relative overflow-hidden">
            <i class="bi bi-building position-absolute opacity-25" style="font-size: 8rem; right: -20px; top: -20px; color: white; transform: rotate(-15deg);"></i>
            <div class="row align-items-center position-relative z-1">
                <div class="col-auto">
                    <img src="${logoSrc}" class="rounded shadow bg-white p-1" style="width: 100px; height: 100px; object-fit: contain;">
                </div>
                <div class="col text-white">
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge bg-white text-success fw-bold me-2 px-3 py-2 rounded-pill shadow-sm">${(data.jenis?.nama || 'Organisasi').toUpperCase()}</span>
                        ${statusBadge}
                    </div>
                    <h2 class="fw-bold mb-1" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">${data.nama}</h2>
                    <h6 class="mb-0" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.3);"><i class="bi bi-geo-alt me-1"></i>${data.kabKota?.name || 'Provinsi Jawa Timur'}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- DETAIL CARD -->
    <div class="row g-4 mb-4">
        <!-- Info Kiri -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-info-circle me-2"></i>Informasi Umum</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Domain Sistem</div>
                            <div class="fw-medium text-dark"><i class="bi bi-diagram-3 me-2 text-primary"></i>${data.jenis?.nama || '-'}</div>
                        </li>
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Skala Organisasi</div>
                            <div class="fw-medium text-dark"><i class="bi bi-globe me-2 text-primary"></i>${data.skala?.nama || 'Belum Ditentukan'}</div>
                        </li>
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Alamat Lengkap</div>
                            <div class="fw-medium text-dark"><i class="bi bi-geo-alt me-2 text-danger"></i>${data.alamat || '-'}</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Info Kanan -->
        <div class="col-md-6">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-telephone-forward me-2"></i>Kontak & Legalitas</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Email / Telepon</div>
                            <div class="fw-medium text-dark"><i class="bi bi-envelope me-2 text-primary"></i>${data.email || '-'} <br> <i class="bi bi-telephone me-2 text-success mt-1"></i>${data.telp || '-'}</div>
                        </li>
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">Narahubung (CP)</div>
                            <div class="fw-medium text-dark"><i class="bi bi-person-lines-fill me-2 text-info"></i>${data.narahubung || '-'}</div>
                        </li>
                        <li class="mb-3">
                            <div class="text-muted small text-uppercase fw-semibold mb-1">SK Pendirian</div>
                            <div class="fw-medium text-dark"><i class="bi bi-file-earmark-text me-2 text-secondary"></i>${data.sk_pendirian || '-'} <br><small class="text-muted ms-4">Tgl: ${tglPendirian}</small></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <!-- CTA KELOLA PENGURUS -->
    <div class="card border-0 shadow-sm bg-primary bg-gradient text-white">
        <div class="card-body p-4 text-center">
            <i class="bi bi-diagram-3-fill fs-1 d-block mb-2 text-white-50"></i>
            <h4 class="fw-bold mb-2">Manajemen Pengurus Organisasi</h4>
            <p class="mb-3 text-white-50">Kelola riwayat kepengurusan (Ketua, Sekretaris, Bendahara) dari periode ke periode pada halaman khusus ini.</p>
            <a href="/admin/organisasi/${data.id}/pengurus" class="btn btn-light btn-lg fw-bold text-primary rounded-pill px-5 shadow-sm">
                <i class="bi bi-box-arrow-up-right me-2"></i>Buka Halaman Pengurus
            </a>
        </div>
    </div>
    `;

    container.innerHTML = html;
}
</script>
@endpush
