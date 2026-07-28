@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">

    <!-- Header Organisasi -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #1f4037 0%, #99f2c8 100%);">
        <div class="card-body p-4 text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <div class="me-3 bg-white p-2 rounded shadow-sm">
                        <img src="{{ $organisasi->logo ? '/storage/'.$organisasi->logo : '/assets/img/default-org.png' }}" style="width:60px;height:60px;object-fit:contain;">
                    </div>
                    <div>
                        <span class="badge bg-white text-success fw-bold me-2 px-3 py-2 rounded-pill shadow-sm mb-2">{{ $organisasi->jenis?->nama ?? 'Organisasi' }}</span>
                        <h3 class="fw-bold mb-1" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">{{ $organisasi->nama }}</h3>
                        <div class="text-white-50"><i class="bi bi-geo-alt me-1"></i>{{ $organisasi->kabKota?->name ?: 'Provinsi Jawa Timur' }}</div>
                    </div>
                </div>
                <div>
                    <a href="{{ route('admin.dashboard') }}?page=organisasi" class="btn btn-outline-light rounded-pill px-4 shadow-sm">
                        <i class="bi bi-arrow-left me-2"></i>Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Utama: Tabel Riwayat Kepengurusan -->
        <div class="col-lg-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Kepengurusan</h5>
                    @if(!$isReadOnly)
                    <button class="btn btn-success btn-sm px-3 rounded-pill shadow-sm" onclick="openPengurusModal()">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Periode
                    </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive p-3">
                        <table id="pengurusTable" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>SK & Periode</th>
                                    <th>Ketua</th>
                                    <th>Sekretaris</th>
                                    <th>Bendahara</th>
                                    <th>Jml Anggota</th>
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
    </div>
</div>

<!-- Modal Tambah/Edit Pengurus -->
<div class="modal fade" id="pengurusModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header text-white bg-success bg-gradient">
                <h5 class="modal-title fw-bold"><i class="bi bi-diagram-3-fill me-2"></i>Form Periode Kepengurusan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="pengurusForm" onsubmit="savePengurus(event)">
                <input type="hidden" id="riwayatId">
                <div class="modal-body">
                    <div class="row g-3">
                        <!-- SK & Periode -->
                        <div class="col-12">
                            <h6 class="fw-bold border-bottom pb-2 text-primary"><i class="bi bi-file-earmark-text me-2"></i>Dasar Hukum & Periode</h6>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">SK Kepengurusan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="sk_kepengurusan" required placeholder="Contoh: SK Nomor 123/ORG/2023">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tgl_awal" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Berakhir</label>
                            <input type="date" class="form-control" id="tgl_akhir">
                        </div>
                        
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold border-bottom pb-2 text-primary"><i class="bi bi-people me-2"></i>Susunan Pengurus Inti</h6>
                            <p class="text-muted small">Cari nama pengurus (Orang) dengan mengetik namanya pada kolom di bawah.</p>
                        </div>

                        <!-- Autocomplete KSB -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ketua</label>
                            <div class="position-relative">
                                <input type="text" class="form-control autocomplete-input" id="search_ketua" placeholder="Ketik nama...">
                                <input type="hidden" id="ketua_id">
                                <div class="autocomplete-results" id="res_ketua" style="display:none; position:absolute; z-index:1050; width:100%; max-height:200px; overflow-y:auto; background:#fff; border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 4px rgba(0,0,0,0.1);"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sekretaris</label>
                            <div class="position-relative">
                                <input type="text" class="form-control autocomplete-input" id="search_sekretaris" placeholder="Ketik nama...">
                                <input type="hidden" id="sekretaris_id">
                                <div class="autocomplete-results" id="res_sekretaris" style="display:none; position:absolute; z-index:1050; width:100%; max-height:200px; overflow-y:auto; background:#fff; border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 4px rgba(0,0,0,0.1);"></div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Bendahara</label>
                            <div class="position-relative">
                                <input type="text" class="form-control autocomplete-input" id="search_bendahara" placeholder="Ketik nama...">
                                <input type="hidden" id="bendahara_id">
                                <div class="autocomplete-results" id="res_bendahara" style="display:none; position:absolute; z-index:1050; width:100%; max-height:200px; overflow-y:auto; background:#fff; border:1px solid #ddd; border-radius:4px; box-shadow:0 2px 4px rgba(0,0,0,0.1);"></div>
                            </div>
                        </div>

                        <div class="col-md-12 mt-3">
                            <label class="form-label fw-semibold">Jumlah Total Anggota</label>
                            <input type="number" class="form-control w-25" id="jumlah_anggota" placeholder="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSave"><i class="bi bi-save me-1"></i>Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .autocomplete-item { padding: 8px 12px; cursor: pointer; border-bottom: 1px solid #eee; }
    .autocomplete-item:hover { background-color: #f8f9fa; color: #0d6efd; }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const ORG_ID = {{ $organisasi->id }};
const IS_READONLY = {{ $isReadOnly ? 'true' : 'false' }};
let _dt, _modal;

$(document).ready(function() {
    _modal = new bootstrap.Modal(document.getElementById('pengurusModal'));
    loadTable();
    setupAutocomplete('ketua');
    setupAutocomplete('sekretaris');
    setupAutocomplete('bendahara');
});

function dtOptions(dateStr) {
    if(!dateStr) return '-';
    return new Date(dateStr).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'});
}

function loadTable() {
    if (_dt) { _dt.ajax.reload(); return; }
    
    _dt = $('#pengurusTable').DataTable({
        ajax: { url: `/admin/organisasi/${ORG_ID}/pengurus/data`, dataSrc: '' },
        columns: [
            { data: null, render: (d,t,r,m) => m.row+1, width: '40px' },
            { data: null, render: r => `<strong>${r.sk_kepengurusan}</strong><br><small class="text-muted">${dtOptions(r.tgl_awal)} s/d ${dtOptions(r.tgl_akhir)}</small>` },
            { data: 'ketua', render: v => v ? `<strong>${v.nama}</strong><br><small class="text-muted">${v.nik||'-'}</small>` : '-' },
            { data: 'sekretaris', render: v => v ? `<strong>${v.nama}</strong><br><small class="text-muted">${v.nik||'-'}</small>` : '-' },
            { data: 'bendahara', render: v => v ? `<strong>${v.nama}</strong><br><small class="text-muted">${v.nik||'-'}</small>` : '-' },
            { data: 'jumlah_anggota', render: v => v ? `${v} Orang` : '-' },
            { data: null, className: 'text-end', orderable: false, render: r => {
                if (IS_READONLY) return '-';
                const dObj = btoa(unescape(encodeURIComponent(JSON.stringify(r))));
                return `
                    <button class="btn btn-sm btn-outline-primary" onclick="editPengurus('${dObj}')"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deletePengurus(${r.id})"><i class="bi bi-trash"></i></button>
                `;
            }}
        ],
        language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json' }
    });
}

function openPengurusModal() {
    document.getElementById('pengurusForm').reset();
    document.getElementById('riwayatId').value = '';
    ['ketua','sekretaris','bendahara'].forEach(role => {
        document.getElementById(`${role}_id`).value = '';
    });
    _modal.show();
}

function editPengurus(dataStr) {
    const r = JSON.parse(decodeURIComponent(escape(atob(dataStr))));
    document.getElementById('riwayatId').value = r.id;
    document.getElementById('sk_kepengurusan').value = r.sk_kepengurusan || '';
    document.getElementById('tgl_awal').value = r.tgl_awal ? r.tgl_awal.substring(0,10) : '';
    document.getElementById('tgl_akhir').value = r.tgl_akhir ? r.tgl_akhir.substring(0,10) : '';
    document.getElementById('jumlah_anggota').value = r.jumlah_anggota || '';
    
    // Set Autocomplete
    ['ketua', 'sekretaris', 'bendahara'].forEach(role => {
        const d = r[role];
        if (d) {
            document.getElementById(`${role}_id`).value = d.id;
            document.getElementById(`search_${role}`).value = `${d.nama} (${d.nik||'-'})`;
        } else {
            document.getElementById(`${role}_id`).value = '';
            document.getElementById(`search_${role}`).value = '';
        }
    });

    _modal.show();
}

async function savePengurus(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menyimpan...';
    
    const payload = {
        id: document.getElementById('riwayatId').value,
        sk_kepengurusan: document.getElementById('sk_kepengurusan').value,
        tgl_awal: document.getElementById('tgl_awal').value,
        tgl_akhir: document.getElementById('tgl_akhir').value || null,
        jumlah_anggota: document.getElementById('jumlah_anggota').value || null,
        ketua_id: document.getElementById('ketua_id').value || null,
        sekretaris_id: document.getElementById('sekretaris_id').value || null,
        bendahara_id: document.getElementById('bendahara_id').value || null,
    };

    try {
        const res = await fetch(`/admin/organisasi/${ORG_ID}/pengurus`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(payload)
        });
        
        if (!res.ok) throw new Error(await res.text());
        
        Swal.fire({icon: 'success', title: 'Tersimpan', showConfirmButton: false, timer: 1500});
        _modal.hide();
        loadTable();
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    } finally {
        btn.disabled = false; btn.innerHTML = '<i class="bi bi-save me-1"></i>Simpan Data';
    }
}

async function deletePengurus(id) {
    const ok = await Swal.fire({
        title: 'Hapus data?', text: "Data kepengurusan ini akan dihapus permanen", icon: 'warning',
        showCancelButton: true, confirmButtonColor: '#dc3545'
    });
    if (!ok.isConfirmed) return;

    try {
        const res = await fetch(`/admin/organisasi/${ORG_ID}/pengurus/${id}`, {
            method: 'DELETE',
            headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
        });
        if (!res.ok) throw new Error('Gagal menghapus');
        loadTable();
    } catch (err) {
        Swal.fire('Error', err.message, 'error');
    }
}

// Autocomplete Logic
function setupAutocomplete(roleName) {
    const input = document.getElementById(`search_${roleName}`);
    const results = document.getElementById(`res_${roleName}`);
    const hiddenId = document.getElementById(`${roleName}_id`);
    let timeout;

    input.addEventListener('input', (e) => {
        clearTimeout(timeout);
        hiddenId.value = ''; // Reset ID if user types
        const val = e.target.value.trim();
        if (val.length < 2) { results.style.display = 'none'; return; }
        
        timeout = setTimeout(async () => {
            try {
                const r = await fetch(`/api/v1/orang?search=${val}&per_page=5`);
                const data = (await r.json()).data || [];
                
                if (data.length === 0) {
                    results.innerHTML = '<div class="p-2 text-muted small">Tidak ditemukan</div>';
                } else {
                    results.innerHTML = data.map(o => `
                        <div class="autocomplete-item" onclick="select${roleName}(${o.id}, '${o.nama}', '${o.nik||''}')">
                            <div class="fw-bold text-dark">${o.nama}</div>
                            <small class="text-muted">${o.nik||'-'} | ${o.kab_kota?.name||'-'}</small>
                        </div>
                    `).join('');
                }
                results.style.display = 'block';
            } catch (err) { console.error(err); }
        }, 300);
    });
}

// Global functions for autocomplete onClick
['ketua', 'sekretaris', 'bendahara'].forEach(role => {
    window[`select${role}`] = function(id, nama, nik) {
        document.getElementById(`${role}_id`).value = id;
        document.getElementById(`search_${role}`).value = `${nama} (${nik})`;
        document.getElementById(`res_${role}`).style.display = 'none';
    }
});

// Close autocomplete when clicking outside
document.addEventListener('click', e => {
    document.querySelectorAll('.autocomplete-results').forEach(el => {
        if (!el.contains(e.target) && e.target.className !== 'form-control autocomplete-input') {
            el.style.display = 'none';
        }
    });
});
</script>
@endpush
