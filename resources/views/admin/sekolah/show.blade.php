@extends('layouts.admin')

@section('title', 'Detail Sekolah')

@section('content')
<div class="row g-4">
    <!-- Kolom Kiri: Detail Sekolah -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">ID: {{ $sekolah->id }}</span>
                    <a href="{{ route('admin.sekolah') }}" class="btn btn-sm btn-light border-0"><i class="bi bi-arrow-left me-1"></i>Kembali</a>
                </div>
                <h5 class="fw-bold text-dark lh-base mb-1">{{ $sekolah->nama_sekolah }}</h5>
                <p class="text-muted small mb-0">{{ $sekolah->kabKota->name ?? '-' }}</p>
            </div>
            <div class="card-body p-4 bg-light bg-opacity-50">
                
                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Jenis & Status</label>
                    <div class="d-flex gap-2">
                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">{{ $sekolah->jenis_sekolah }}</span>
                        <span class="badge bg-{{ $sekolah->status_sekolah === 'Negeri' ? 'primary' : 'warning' }} bg-opacity-10 text-{{ $sekolah->status_sekolah === 'Negeri' ? 'primary' : 'warning' }} px-3 py-2">{{ $sekolah->status_sekolah }}</span>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-2">Kontak</label>
                    <div class="bg-white p-3 rounded-3 border">
                        <div class="mb-2">
                            <small class="text-muted d-block">Narahubung</small>
                            <span class="fw-medium">{{ $sekolah->narahubung ?: '-' }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Telepon</small>
                            <span class="fw-medium">{{ $sekolah->telepon ?: '-' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Ekstrakurikuler -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
            <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-primary me-2"></i>Data Ekstrakurikuler</h6>
                @if(!auth()->user()->hasAnyRole(['Kepala Dinas Provinsi', 'Kepala Bidang Olahraga Masyarakat', 'Kepala Dinas Kab/Kota']))
                <button class="btn btn-primary btn-sm rounded-pill px-3" onclick="openEkskulModal()">
                    <i class="bi bi-plus-circle me-1"></i>Tambah Ekskul
                </button>
                @endif
            </div>
            
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle w-100" id="ekskulTable">
                        <thead class="table-light">
                            <tr>
                                <th>Jenis</th>
                                <th>Pembina/Pelatih</th>
                                <th>Anggota</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form Ekskul -->
<div class="modal fade" id="ekskulModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-gradient-primary text-white border-0">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-plus-circle me-2"></i>Tambah Ekstrakurikuler</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="ekskulForm">
                    <input type="hidden" name="id" id="ekskulId">
                    <input type="hidden" name="sekolah_id" value="{{ $sekolah->id }}">
                    <div id="formError" class="alert alert-danger d-none small"></div>
                    
                    <div class="row g-3">
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold text-secondary">Jenis Ekstrakurikuler <span class="text-danger">*</span></label>
                            <select name="jenis_ekskul_id" id="jenisEkskulSelect" class="form-select" required>
                                <option value="">Pilih Jenis...</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Nama Pembina <span class="text-danger">*</span></label>
                            <input type="text" name="nama_pembina" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Jadwal Pertemuan</label>
                            <input type="text" name="jadwal_pertemuan" class="form-control" placeholder="Contoh: Senin & Rabu, 15:00">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Jumlah Anggota Putra</label>
                            <input type="number" name="jumlah_anggota_putra" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Jumlah Anggota Putri</label>
                            <input type="number" name="jumlah_anggota_putri" class="form-control" min="0" value="0">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label small fw-bold text-secondary">Narahubung Pelatih/Ketua/Pembina/Guru</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" name="narahubung" class="form-control" placeholder="Nama Narahubung">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="telepon" class="form-control" placeholder="Nomor Telepon">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Status <span class="text-danger">*</span></label>
                            <select name="status_ekstrakurikuler" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Non-Aktif">Non-Aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-secondary">Dokumen Pendukung (Opsional)</label>
                            <input type="file" name="dokumen_jumlah_anggota" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text small" id="dokumenLinkContainer">Max 5MB. PDF/Image.</div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary px-4 btn-submit-lock" id="btnSaveEkskul" onclick="saveEkskul(this)">Simpan</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const sekolahId = {{ $sekolah->id }};
    const isReadOnly = {{ auth()->user()->hasAnyRole(['Kepala Dinas Provinsi', 'Kepala Bidang Olahraga Masyarakat', 'Kepala Dinas Kab/Kota']) ? 'true' : 'false' }};
    let dataTable;
    let ekskulModalObj;

    document.addEventListener('DOMContentLoaded', function() {
        ekskulModalObj = new bootstrap.Modal(document.getElementById('ekskulModal'));

        // Load jenis ekskul for dropdown
        fetch('/api/v1/jenis-ekstrakurikuler?all=1')
            .then(res => res.json())
            .then(data => {
                const sel = document.getElementById('jenisEkskulSelect');
                data.forEach(j => {
                    const opt = document.createElement('option');
                    opt.value = j.id;
                    opt.textContent = `${j.nama} (${j.kategori})`;
                    sel.appendChild(opt);
                });
            });

        // Init DataTable
        dataTable = $('#ekskulTable').DataTable({
            serverSide: true,
            ajax: function(dtParams, callback, settings) {
                const page = Math.floor(dtParams.start / dtParams.length) + 1;
                const params = new URLSearchParams({ page, per_page: dtParams.length, sekolah_id: sekolahId });
                if (dtParams.search?.value) params.set('search', dtParams.search.value);
                
                fetch(`/api/v1/ekstrakurikuler?${params}`, {
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
            columns: [
                { data: 'jenis_ekskul', render: v => v && v.nama ? `<strong>${v.nama}</strong><br><small class="text-muted">${v.kategori}</small>` : '-' },
                { data: 'nama_pembina', render: (v,t,r) => `${v}<br><small class="text-muted">${r.narahubung ? '<i class="bi bi-telephone"></i> '+r.narahubung : ''}</small>` },
                { data: 'jumlah_anggota_putra', render: (v,t,r) => `<span class="badge bg-primary bg-opacity-10 text-primary me-1" title="Putra">L: ${v||0}</span> <span class="badge bg-danger bg-opacity-10 text-danger" title="Putri">P: ${r.jumlah_anggota_putri||0}</span><br><small class="fw-bold">Total: ${(v||0)+(r.jumlah_anggota_putri||0)}</small>` },
                { data: 'jadwal_pertemuan', defaultContent: '-' },
                { data: 'status_ekstrakurikuler', render: v => `<span class="badge bg-${v==='Aktif'?'success':'secondary'} bg-opacity-10 text-${v==='Aktif'?'success':'secondary'}">${v}</span>` },
                { data: null, orderable: false, render: (d,t,r) => {
                    if(isReadOnly) return '-';
                    return `
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-light border-0 text-primary" onclick="editEkskul(${r.id})" title="Edit"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-light border-0 text-danger btn-delete-lock" onclick="deleteEkskul(${r.id}, this)" title="Hapus"><i class="bi bi-trash-fill"></i></button>
                        ${r.dokumen_jumlah_anggota ? `<a href="/storage/${r.dokumen_jumlah_anggota}" target="_blank" class="btn btn-sm btn-light border-0 text-info" title="Lihat Dokumen"><i class="bi bi-file-earmark-text"></i></a>` : ''}
                    </div>`;
                }}
            ],
            processing: true,
            pageLength: 10,
            language: {
                search: '', searchPlaceholder: 'Cari pembina...', lengthMenu: '_MENU_',
                emptyTable: 'Belum ada ekstrakurikuler di sekolah ini', zeroRecords: 'Pencarian tidak ditemukan',
                processing: '<span class="spinner-border spinner-border-sm text-primary"></span>',
                paginate:{first:'«',last:'»',next:'›',previous:'‹'}
            },
            dom: "<'row mb-3'<'col-sm-6'l><'col-sm-6 d-flex justify-content-end'f>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row mt-3'<'col-sm-5'i><'col-sm-7 d-flex justify-content-end'p>>",
        });
    });

    function openEkskulModal() {
        document.getElementById('ekskulForm').reset();
        document.getElementById('ekskulId').value = '';
        document.getElementById('modalTitle').innerHTML = '<i class="bi bi-plus-circle me-2"></i>Tambah Ekstrakurikuler';
        document.getElementById('formError').classList.add('d-none');
        document.getElementById('dokumenLinkContainer').innerHTML = 'Max 5MB. PDF/Image.';
        
        // Reset btn UI state
        const btn = document.getElementById('btnSaveEkskul');
        btn.disabled = false;
        btn.innerHTML = 'Simpan';

        ekskulModalObj.show();
    }

    function editEkskul(id) {
        fetch(`/api/v1/ekstrakurikuler/${id}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById('ekskulForm').reset();
                document.getElementById('formError').classList.add('d-none');
                
                const form = document.getElementById('ekskulForm');
                form.elements['id'].value = data.id;
                form.elements['jenis_ekskul_id'].value = data.jenis_ekskul_id;
                form.elements['nama_pembina'].value = data.nama_pembina || '';
                form.elements['jadwal_pertemuan'].value = data.jadwal_pertemuan || '';
                form.elements['jumlah_anggota_putra'].value = data.jumlah_anggota_putra || 0;
                form.elements['jumlah_anggota_putri'].value = data.jumlah_anggota_putri || 0;
                form.elements['narahubung'].value = data.narahubung || '';
                form.elements['telepon'].value = data.telepon || '';
                form.elements['status_ekstrakurikuler'].value = data.status_ekstrakurikuler || 'Aktif';
                
                if (data.dokumen_jumlah_anggota) {
                    document.getElementById('dokumenLinkContainer').innerHTML = `<a href="/storage/${data.dokumen_jumlah_anggota}" target="_blank">Lihat Dokumen Saat Ini</a> (Upload baru untuk mengganti)`;
                } else {
                    document.getElementById('dokumenLinkContainer').innerHTML = 'Max 5MB. PDF/Image.';
                }

                document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil-square me-2"></i>Edit Ekstrakurikuler';
                
                // Reset btn UI state
                const btn = document.getElementById('btnSaveEkskul');
                btn.disabled = false;
                btn.innerHTML = 'Simpan Perubahan';

                ekskulModalObj.show();
            });
    }

    function saveEkskul(btn) {
        // Anti Double Click UI logic
        if(btn.disabled) return;
        
        const form = document.getElementById('ekskulForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const originalBtnHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

        const id = document.getElementById('ekskulId').value;
        const formData = new FormData(form);
        const errorAlert = document.getElementById('formError');
        errorAlert.classList.add('d-none');

        let url = '/api/v1/ekstrakurikuler';
        if (id) {
            url += '/' + id;
            formData.append('_method', 'PUT'); // untuk upload file di PUT
        }

        fetch(url, {
            method: 'POST', // always POST with FormData, spoofing PUT if needed
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: formData
        })
        .then(res => res.json().then(data => ({status: res.status, body: data})))
        .then(result => {
            if (result.status === 429) { // Rate limit / lock
                errorAlert.innerHTML = result.body.message;
                errorAlert.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            } else if (result.status >= 400) {
                let msg = result.body.message || 'Terjadi kesalahan';
                if (result.body.errors) {
                    msg = Object.values(result.body.errors).map(e => `<li>${e}</li>`).join('');
                    msg = `<ul class="mb-0 ps-3">${msg}</ul>`;
                }
                errorAlert.innerHTML = msg;
                errorAlert.classList.remove('d-none');
                btn.disabled = false;
                btn.innerHTML = originalBtnHtml;
            } else {
                Toast.fire({icon: 'success', title: 'Data berhasil disimpan'});
                ekskulModalObj.hide();
                dataTable.ajax.reload(null, false);
                // Biarkan disabled true sampe modal hilang (anti spam animasi)
                setTimeout(() => { btn.disabled = false; btn.innerHTML = originalBtnHtml; }, 500);
            }
        })
        .catch(err => {
            console.error(err);
            errorAlert.innerHTML = 'Terjadi kesalahan jaringan.';
            errorAlert.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = originalBtnHtml;
        });
    }

    function deleteEkskul(id, btn) {
        if(btn.disabled) return;
        
        Swal.fire({
            title: 'Hapus Data?', text: "Data tidak dapat dikembalikan!",
            icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                const originalBtnHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch(`/api/v1/ekstrakurikuler/${id}`, {
                    method: 'DELETE',
                    headers: {'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken}
                })
                .then(res => {
                    if (res.ok) {
                        Toast.fire({icon: 'success', title: 'Berhasil dihapus'});
                        dataTable.ajax.reload(null, false);
                    } else {
                        Toast.fire({icon: 'error', title: 'Gagal menghapus'});
                        btn.disabled = false;
                        btn.innerHTML = originalBtnHtml;
                    }
                })
                .catch(() => {
                    Toast.fire({icon: 'error', title: 'Terjadi kesalahan jaringan'});
                    btn.disabled = false;
                    btn.innerHTML = originalBtnHtml;
                });
            }
        });
    }
</script>
@endpush
