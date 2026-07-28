@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    <!-- Header Event -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge bg-white text-primary fw-bold me-2 px-3 py-2 rounded-pill shadow-sm mb-2">${{ $event->jenis_event }}</span>
                    <h3 class="fw-bold mb-1">{{ $event->nama }}</h3>
                    <div class="text-white-50"><i class="bi bi-geo-alt me-1"></i>{{ $event->lokasi_kegiatan ?: '-' }}</div>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}?page=events" class="btn btn-outline-light rounded-pill px-4">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Utama: Tabel Peserta -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-people-fill me-2 text-primary"></i>Daftar Peserta & Hasil</h5>
                    @if(!$isReadOnly)
                    <button class="btn btn-success btn-sm px-3 rounded-pill shadow-sm" onclick="openPesertaModal()">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Peserta
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table id="pesertaTable" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Peserta</th>
                                    <th>Cabor / Kategori</th>
                                    <th>Kontingen</th>
                                    <th>Medali</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Dimuat via DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Klasemen Medali -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-bar-chart-fill me-2 text-warning"></i>Klasemen Medali</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-borderless mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Kontingen</th>
                                    <th class="text-center" title="Emas">🥇</th>
                                    <th class="text-center" title="Perak">🥈</th>
                                    <th class="text-center" title="Perunggu">🥉</th>
                                </tr>
                            </thead>
                            <tbody id="klasemenBody">
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Menghitung klasemen...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Peserta -->
<div class="modal fade" id="pesertaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white bg-success bg-gradient">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus-fill me-2"></i>Form Peserta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="pesertaForm" onsubmit="savePeserta(event)">
                <input type="hidden" id="riwayatId">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12 position-relative">
                            <label class="form-label fw-semibold">Cari Atlet (Nama/NIK) <span class="text-danger">*</span></label>
                            <input class="form-control form-control-lg shadow-sm" id="pesertaSearch" placeholder="Ketik NIK atau nama..." oninput="debounceSearchOrang(this)" autocomplete="off">
                            <div class="autocomplete-results" id="pesertaResults" style="display:none; position:absolute; z-index:1000; width:95%; background:white; border:1px solid #ddd; border-radius:4px; max-height:200px; overflow-y:auto; box-shadow:0 4px 6px rgba(0,0,0,0.1);"></div>
                            <input type="hidden" id="pesertaOrangId" required>
                            <div id="pesertaSelected" class="mt-2 fw-medium text-success"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cabang Olahraga <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="pesertaCabor" required>
                                <option value="">— Pilih Cabor —</option>
                                @foreach($event->cabors as $c)
                                <option value="{{ $c->id }}">{{ $c->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori / Nomor</label>
                            <input class="form-control shadow-sm" id="pesertaKategori" placeholder="Contoh: -60kg Gaya Bebas">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Kontingen Asal (Kab/Kota)</label>
                            <select class="form-select shadow-sm" id="pesertaKabKota">
                                <option value="">— Opsional (Pilih Kab/Kota) —</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Prestasi</label>
                            <input class="form-control shadow-sm" id="pesertaPrestasi" placeholder="Contoh: Juara 1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Perolehan Medali</label>
                            <select class="form-select shadow-sm" id="pesertaMedali">
                                <option value="-">— Tanpa Medali —</option>
                                <option value="emas">🥇 Emas</option>
                                <option value="perak">🥈 Perak</option>
                                <option value="perunggu">🥉 Perunggu</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Tanggal Tanding</label>
                            <input type="date" class="form-control shadow-sm" id="pesertaTanggal">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Catatan Rekor Waktu / Poin</label>
                            <input class="form-control shadow-sm" id="pesertaWaktu" placeholder="Contoh: 10.5 Detik">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm" id="btnSave">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const eventId = {{ $event->id }};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
let dt = null;
let kabKotaCache = [];

document.addEventListener('DOMContentLoaded', async () => {
    // Load Kab/Kota cache for dropdown
    try {
        const r = await fetch('/api/v1/public/kab-kota?all', {headers:{'Accept':'application/json'}});
        kabKotaCache = await r.json();
        const sel = document.getElementById('pesertaKabKota');
        kabKotaCache.forEach(k => {
            sel.add(new Option(k.name, k.id));
        });
    } catch(e) {}

    // Init DataTables
    dt = $('#pesertaTable').DataTable({
        processing: true, serverSide: false, // For simplicity we fetch all and let client sort
        ajax: {
            url: `/api/v1/events/${eventId}/riwayat`,
            dataSrc: '' // Return is plain JSON array
        },
        columns: [
            { data:null, render:(d,t,r,m)=>m.row+1, width:'40px' },
            { data:'orang.nama', render:(v,t,r)=>`<strong>${v||'?'}</strong><br><small class="text-muted">${r.orang?.nik||''}</small>` },
            { data:'cabor.nama', render:(v,t,r)=>`<span class="badge bg-primary bg-opacity-10 text-primary">${v||'-'}</span><br><small class="text-muted">${r.kategori||'-'}</small>` },
            { data:'kab_kota.name', defaultContent:'<span class="text-muted">-</span>' },
            { data:'medali', render:v=>{
                if(v==='emas') return '<span class="badge bg-warning text-dark border border-warning shadow-sm"><i class="bi bi-award-fill me-1"></i>Emas</span>';
                if(v==='perak') return '<span class="badge bg-secondary text-white shadow-sm"><i class="bi bi-award-fill me-1"></i>Perak</span>';
                if(v==='perunggu') return '<span class="badge text-white shadow-sm" style="background:#cd7f32"><i class="bi bi-award-fill me-1"></i>Perunggu</span>';
                return '<span class="text-muted">-</span>';
            }},
            { data:null, orderable:false, className:'text-end', render:(d,t,r)=>`
                <button class="btn btn-sm btn-outline-primary" onclick="editPeserta(${r.id})" title="Edit"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deletePeserta(${r.id})" title="Hapus"><i class="bi bi-trash"></i></button>
            `}
        ],
        drawCallback: function() {
            rebuildKlasemen(this.api().rows().data().toArray());
        }
    });
});

function rebuildKlasemen(data) {
    const table = document.getElementById('klasemenBody');
    if(!data || data.length === 0) {
        table.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada perolehan medali</td></tr>';
        return;
    }
    
    // Hitung medali per kab/kota
    const summary = {}; // { 'Kota Surabaya': {e:0, p:0, b:0} }
    data.forEach(r => {
        if(!r.medali || r.medali === '-') return;
        const kname = r.kab_kota?.name || 'Tanpa Kontingen';
        if(!summary[kname]) summary[kname] = {e:0, p:0, b:0};
        
        if(r.medali === 'emas') summary[kname].e++;
        else if(r.medali === 'perak') summary[kname].p++;
        else if(r.medali === 'perunggu') summary[kname].b++;
    });
    
    // Convert ke array dan sort (Emas -> Perak -> Perunggu)
    const arr = Object.keys(summary).map(k => ({name: k, ...summary[k]}));
    arr.sort((a,b) => {
        if(b.e !== a.e) return b.e - a.e;
        if(b.p !== a.p) return b.p - a.p;
        return b.b - a.b;
    });
    
    if(arr.length === 0) {
        table.innerHTML = '<tr><td colspan="4" class="text-center py-4 text-muted">Belum ada perolehan medali</td></tr>';
        return;
    }
    
    table.innerHTML = arr.map((x, i) => `
        <tr>
            <td class="ps-3"><span class="fw-bold me-2 ${i<3?'text-primary':''}">${i+1}.</span> <span class="fw-medium">${x.name}</span></td>
            <td class="text-center fw-bold text-dark">${x.e}</td>
            <td class="text-center fw-bold text-secondary">${x.p}</td>
            <td class="text-center fw-bold" style="color:#a56628">${x.b}</td>
        </tr>
    `).join('');
}

// ==========================
// FORM CRUD PESERTA
// ==========================
function openPesertaModal() {
    document.getElementById('pesertaForm').reset();
    document.getElementById('riwayatId').value = '';
    document.getElementById('pesertaOrangId').value = '';
    document.getElementById('pesertaSelected').innerHTML = '';
    document.getElementById('pesertaSearch').disabled = false;
    new bootstrap.Modal(document.getElementById('pesertaModal')).show();
}

async function editPeserta(id) {
    const r = dt.row((idx, data) => data.id === id).data();
    if(!r) return;
    
    document.getElementById('riwayatId').value = r.id;
    document.getElementById('pesertaOrangId').value = r.orang_id;
    document.getElementById('pesertaSearch').value = `${r.orang?.nama} (${r.orang?.nik||''})`;
    document.getElementById('pesertaSearch').disabled = true;
    document.getElementById('pesertaSelected').innerHTML = `<small class="text-success"><i class="bi bi-check-circle me-1"></i>Edit data untuk atlet ini</small>`;
    
    document.getElementById('pesertaCabor').value = r.cabor_id || '';
    document.getElementById('pesertaKategori').value = r.kategori || '';
    document.getElementById('pesertaKabKota').value = r.kab_kota_id || '';
    document.getElementById('pesertaPrestasi').value = r.prestasi || '';
    document.getElementById('pesertaMedali').value = r.medali || '-';
    document.getElementById('pesertaTanggal').value = r.tanggal || '';
    document.getElementById('pesertaWaktu').value = r.waktu || '';
    
    new bootstrap.Modal(document.getElementById('pesertaModal')).show();
}

async function savePeserta(e) {
    e.preventDefault();
    const id = document.getElementById('riwayatId').value;
    const payload = {
        orang_id: document.getElementById('pesertaOrangId').value,
        event_id: eventId,
        cabor_id: document.getElementById('pesertaCabor').value,
        kategori: document.getElementById('pesertaKategori').value,
        kab_kota_id: document.getElementById('pesertaKabKota').value,
        prestasi: document.getElementById('pesertaPrestasi').value,
        medali: document.getElementById('pesertaMedali').value,
        tanggal: document.getElementById('pesertaTanggal').value,
        waktu: document.getElementById('pesertaWaktu').value,
    };
    
    const url = id ? `/api/v1/riwayat-event/${id}` : `/api/v1/events/${eventId}/riwayat/batch`;
    const method = id ? 'PUT' : 'POST';
    const body = id ? payload : {riwayats: [payload]}; // Batch API for create
    
    const btn = document.getElementById('btnSave');
    btn.disabled = true;
    btn.innerHTML = 'Menyimpan...';
    
    try {
        const res = await fetch(url, {
            method: method,
            headers: {'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
            body: JSON.stringify(body)
        });
        if(res.ok) {
            bootstrap.Modal.getInstance(document.getElementById('pesertaModal')).hide();
            dt.ajax.reload(null, false);
        } else {
            const j = await res.json();
            alert(j.message || 'Gagal menyimpan');
        }
    } catch(err) {
        alert('Kesalahan jaringan');
    }
    btn.disabled = false;
    btn.innerHTML = 'Simpan Data';
}

async function deletePeserta(id) {
    if(!confirm('Hapus peserta ini?')) return;
    try {
        const r=await fetch(`/api/v1/riwayat-event/${id}`,{method:'DELETE',headers:{'X-CSRF-TOKEN':csrfToken}});
        if(r.ok) { dt.ajax.reload(null, false); }
    } catch(e){}
}

// ==========================
// SEARCH ORANG AUTOCOMPLETE
// ==========================
let searchTimer;
function debounceSearchOrang(input) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => doSearchOrang(input), 300);
}

async function doSearchOrang(input) {
    const q = input.value;
    const resEl = document.getElementById('pesertaResults');
    if(q.length < 2) { resEl.style.display = 'none'; return; }
    
    try {
        const r = await fetch(`/api/v1/cari-orang?q=${encodeURIComponent(q)}`);
        const list = await r.json();
        
        if(!list.length) {
            resEl.innerHTML = '<div class="p-2 text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Atlet tidak ditemukan</div>';
        } else {
            resEl.innerHTML = list.map(o=>`
                <div class="p-2 border-bottom" style="cursor:pointer;" onclick="selectOrang('${o.id}','${o.nama.replace(/'/g, "\\'")}','${o.nik||''}')" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background='white'">
                    <div class="fw-bold">${o.nama}</div>
                    <div class="small text-muted">${o.nik||'No NIK'} · ${o.domisili?.name||'-'}</div>
                </div>`).join('');
        }
        resEl.style.display = 'block';
    } catch(e) {}
}

window.selectOrang = function(id, nama, nik) {
    document.getElementById('pesertaOrangId').value = id;
    document.getElementById('pesertaSearch').value = `${nama} (${nik})`;
    document.getElementById('pesertaResults').style.display = 'none';
    document.getElementById('pesertaSelected').innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Atlet Terpilih: ${nama}`;
}

// Close autocomplete when clicking outside
document.addEventListener('click', function(e) {
    if(!e.target.closest('#pesertaSearch') && !e.target.closest('#pesertaResults')) {
        document.getElementById('pesertaResults').style.display = 'none';
    }
});
</script>
<style>
.autocomplete-item:hover { background: #f8f9fa; }
</style>
@endpush
