@extends('layouts.admin')

@section('title', 'Aduan & Pusat Bantuan')

@section('content')
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="fw-bold mb-1">Daftar Tiket Aduan</h5>
                <p class="text-muted small mb-0">Kelola dan pantau status laporan kendala sistem</p>
            </div>
            @if(!auth()->user()->hasRole('SuperAdmin'))
            <button class="btn btn-primary px-4 py-2" data-bs-toggle="modal" data-bs-target="#createTicketModal">
                <i class="bi bi-plus-lg me-2"></i>Buat Tiket Baru
            </button>
            @endif
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tiket</th>
                        <th>Pelapor</th>
                        <th>Judul & Kategori</th>
                        <th>Status</th>
                        <th>Waktu Dibuat</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tickets as $t)
                    <tr>
                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">{{ $t->kode_tiket }}</span></td>
                        <td>
                            <strong>{{ $t->user->name ?? 'Unknown' }}</strong><br>
                            <small class="text-muted">{{ $t->user->roles[0]->name ?? '-' }}</small>
                        </td>
                        <td>
                            <strong class="d-block text-truncate" style="max-width: 250px;">{{ $t->judul }}</strong>
                            <small class="text-muted">{{ $t->kategori }}</small>
                        </td>
                        <td>
                            @if($t->status === 'open')
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><i class="bi bi-envelope-open me-1"></i>Open</span>
                            @elseif($t->status === 'in_progress')
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $t->created_at->format('d M Y, H:i') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.aduan.show', $t->id) }}" class="btn btn-sm btn-light border-0 shadow-sm text-primary">
                                <i class="bi bi-chat-text-fill me-1"></i> Buka Diskusi
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <h6 class="text-muted">Belum ada tiket aduan yang tercatat.</h6>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL BUAT TIKET --}}
<div class="modal fade" id="createTicketModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.aduan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white border-0 py-3">
                    <h5 class="modal-title fw-semibold"><i class="bi bi-plus-circle me-2"></i>Buat Tiket Baru</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 bg-light bg-opacity-50">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium text-dark">Judul Aduan <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" placeholder="Contoh: Gagal menyimpan data sarana" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-dark">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="">Pilih Kategori...</option>
                                <option value="Error Sistem">Error Sistem</option>
                                <option value="Bantuan Penggunaan">Bantuan Penggunaan</option>
                                <option value="Permintaan Fitur">Permintaan Fitur</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-dark">Deskripsi Lengkap <span class="text-danger">*</span></label>
                            <textarea name="deskripsi" class="form-control" rows="5" placeholder="Jelaskan secara detail kendala yang dialami, termasuk urutan langkah sebelum error terjadi..." required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-dark">Lampiran Screenshot <span class="text-muted fw-normal">(Opsional)</span></label>
                            <input type="file" name="lampiran" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text text-muted small"><i class="bi bi-info-circle me-1"></i>Format yang diizinkan: JPG, PNG, PDF (Maks 2MB). Sangat disarankan untuk melampirkan screenshot.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-send-fill me-2"></i>Kirim Tiket</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
