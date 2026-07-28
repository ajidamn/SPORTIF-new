@extends('layouts.admin')

@section('title', 'Detail Aduan')

@section('content')
<div class="row g-4">
    <!-- Kolom Kiri: Detail Aduan -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-white border-bottom p-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1">{{ $ticket->kode_tiket }}</span>
                    @if($ticket->status === 'open')
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill"><i class="bi bi-envelope-open me-1"></i>Open</span>
                    @elseif($ticket->status === 'in_progress')
                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill"><i class="bi bi-arrow-repeat me-1"></i>Diproses</span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i>Selesai</span>
                    @endif
                </div>
                <h5 class="fw-bold text-dark lh-base mb-0">{{ $ticket->judul }}</h5>
            </div>
            <div class="card-body p-4 bg-light bg-opacity-50">
                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Informasi Pelapor</label>
                    <div class="d-flex align-items-center gap-3 bg-white p-3 rounded-3 border">
                        <div class="avatar-md bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;font-size:1.2rem;font-weight:600">
                            {{ substr($ticket->user->name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">{{ $ticket->user->name ?? 'Unknown' }}</h6>
                            <small class="text-muted">{{ $ticket->user->roles[0]->name ?? '-' }}</small>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-1">Kategori</label>
                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">{{ $ticket->kategori }}</span>
                </div>

                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-2">Deskripsi Aduan</label>
                    <div class="bg-white p-3 rounded-3 border">
                        <p class="mb-0 text-dark" style="white-space: pre-wrap; line-height: 1.6;">{{ $ticket->deskripsi }}</p>
                    </div>
                </div>

                @if($ticket->lampiran)
                <div class="mb-4">
                    <label class="text-muted small fw-semibold text-uppercase d-block mb-2">Lampiran</label>
                    <a href="{{ asset('storage/' . $ticket->lampiran) }}" target="_blank" class="btn btn-outline-primary btn-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-paperclip"></i>Lihat Lampiran Asli
                    </a>
                </div>
                @endif
            </div>
            
            @if(auth()->user()->hasRole('SuperAdmin') && $ticket->status !== 'closed')
            <div class="card-footer bg-white border-top p-4 text-end">
                <form action="{{ route('admin.aduan.close', $ticket->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin masalah ini sudah selesai dan ingin menutup tiket?')">
                    @csrf
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-check2-all me-2"></i>Tandai Selesai (Tutup Tiket)</button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Thread/Diskusi -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
            <div class="card-header bg-white border-bottom p-4">
                <h6 class="fw-bold mb-0"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Ruang Diskusi & Penanganan</h6>
            </div>
            
            <div class="card-body p-4 overflow-auto" id="chatContainer" style="max-height: 600px; background-color: #f8fafc;">
                @if(session('success'))
                    <div class="alert alert-success py-2 px-3 small">{{ session('success') }}</div>
                @endif

                @forelse($ticket->replies as $reply)
                    @php $isOwn = $reply->user_id === auth()->id(); @endphp
                    <div class="d-flex gap-3 mb-4 {{ $isOwn ? 'flex-row-reverse' : '' }}">
                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white {{ $reply->user->hasRole('SuperAdmin') ? 'bg-danger' : 'bg-primary' }}" style="width:40px;height:40px;min-width:40px">
                            {{ substr($reply->user->name ?? 'U', 0, 1) }}
                        </div>
                        <div style="max-width: 80%;">
                            <div class="d-flex align-items-baseline gap-2 mb-1 {{ $isOwn ? 'justify-content-end' : '' }}">
                                <span class="fw-bold small {{ $reply->user->hasRole('SuperAdmin') ? 'text-danger' : 'text-dark' }}">{{ $reply->user->name ?? 'Unknown' }}</span>
                                <span class="text-muted" style="font-size: 0.75rem">{{ $reply->created_at->format('d M, H:i') }}</span>
                            </div>
                            <div class="p-3 rounded-4 shadow-sm {{ $isOwn ? 'bg-primary text-white' : 'bg-white border text-dark' }}">
                                <p class="mb-0" style="white-space: pre-wrap;">{{ $reply->pesan }}</p>
                                @if($reply->lampiran)
                                    <hr class="{{ $isOwn ? 'border-light opacity-25' : 'border-secondary opacity-25' }} my-2">
                                    <a href="{{ asset('storage/' . $reply->lampiran) }}" target="_blank" class="small text-decoration-none {{ $isOwn ? 'text-light' : 'text-primary' }}">
                                        <i class="bi bi-paperclip me-1"></i>Buka Lampiran Tambahan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted" id="emptyChatState">
                        <i class="bi bi-chat-square-text fs-2 mb-2 d-block opacity-50"></i>
                        <p class="small">Belum ada diskusi atau tanggapan pada tiket ini.<br>Silakan ketik pesan di bawah untuk memulai.</p>
                    </div>
                @endforelse
            </div>

            @if($ticket->status !== 'closed')
            <div class="card-footer bg-white border-top p-3">
                <form id="replyForm" action="{{ route('admin.aduan.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="d-flex flex-column gap-2">
                        <textarea name="pesan" class="form-control bg-light border-0" rows="3" placeholder="Ketik pesan tanggapan Anda di sini..." required style="resize:none"></textarea>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <input type="file" name="lampiran" id="fileLampiran" class="d-none" accept=".jpg,.jpeg,.png,.pdf" onchange="document.getElementById('fileName').textContent = this.files[0] ? this.files[0].name : ''">
                                <label for="fileLampiran" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                    <i class="bi bi-paperclip me-1"></i>Lampirkan File
                                </label>
                                <span id="fileName" class="small text-muted ms-2"></span>
                            </div>
                            <button type="submit" class="btn btn-primary px-4 rounded-pill">Kirim <i class="bi bi-send-fill ms-1"></i></button>
                        </div>
                    </div>
                </form>
            </div>
            @else
            <div class="card-footer bg-light border-top p-4 text-center">
                <p class="text-muted small mb-0"><i class="bi bi-lock-fill me-1"></i>Tiket ini telah ditutup. Percakapan tidak dapat dilanjutkan.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chatContainer');
    
    // Auto-scroll to bottom on load
    if (chatContainer) {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    @if($ticket->status !== 'closed')
    // Ambil ID balasan terakhir (jika ada), default 0
    let lastReplyId = {{ $ticket->replies->last()->id ?? 0 }};
    const ticketId = {{ $ticket->id }};
    const authUserId = {{ auth()->id() }};
    
    // Fungsi memuat pesan baru
    function fetchNewMessages() {
        fetch(`/admin/aduan/${ticketId}/messages?last_id=${lastReplyId}`)
            .then(res => res.json())
            .then(data => {
                if (data.replies && data.replies.length > 0) {
                    let hasNew = false;
                    
                    data.replies.forEach(reply => {
                        // Perbarui ID terakhir
                        if (reply.id > lastReplyId) {
                            lastReplyId = reply.id;
                            hasNew = true;

                            // Hapus placeholder kosong jika ada
                            const emptyState = document.getElementById('emptyChatState');
                            if (emptyState) emptyState.remove();

                            const isOwn = reply.is_own;
                            const flexDir = isOwn ? 'flex-row-reverse' : '';
                            const justifyEnd = isOwn ? 'justify-content-end' : '';
                            const avatarColor = reply.is_superadmin ? 'bg-danger' : 'bg-primary';
                            const nameColor = reply.is_superadmin ? 'text-danger' : 'text-dark';
                            const bubbleClass = isOwn ? 'bg-primary text-white' : 'bg-white border text-dark';
                            
                            let lampiranHtml = '';
                            if (reply.lampiran) {
                                const hrClass = isOwn ? 'border-light opacity-25' : 'border-secondary opacity-25';
                                const aClass = isOwn ? 'text-light' : 'text-primary';
                                lampiranHtml = `
                                    <hr class="${hrClass} my-2">
                                    <a href="${reply.lampiran}" target="_blank" class="small text-decoration-none ${aClass}">
                                        <i class="bi bi-paperclip me-1"></i>Buka Lampiran Tambahan
                                    </a>
                                `;
                            }

                            const html = `
                            <div class="d-flex gap-3 mb-4 ${flexDir}">
                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center text-white ${avatarColor}" style="width:40px;height:40px;min-width:40px">
                                    ${reply.user_initial}
                                </div>
                                <div style="max-width: 80%;">
                                    <div class="d-flex align-items-baseline gap-2 mb-1 ${justifyEnd}">
                                        <span class="fw-bold small ${nameColor}">${reply.user_name}</span>
                                        <span class="text-muted" style="font-size: 0.75rem">${reply.created_at}</span>
                                    </div>
                                    <div class="p-3 rounded-4 shadow-sm ${bubbleClass}">
                                        <p class="mb-0" style="white-space: pre-wrap;">${reply.pesan}</p>
                                        ${lampiranHtml}
                                    </div>
                                </div>
                            </div>
                            `;
                            
                            chatContainer.insertAdjacentHTML('beforeend', html);
                        }
                    });

                    // Scroll kebawah jika ada pesan baru
                    if (hasNew) {
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                }
                
                // Jika status tiket ditutup oleh pihak lain
                if (data.status === 'closed') {
                    location.reload(); // Reload untuk me-render UI tiket tertutup
                }
            })
            .catch(err => console.error("Error fetching messages:", err));
    }

    // Polling setiap 5 detik
    setInterval(fetchNewMessages, 5000);

    // AJAX Form Submission untuk mencegah reload halaman
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = replyForm.querySelector('button[type="submit"]');
            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            submitBtn.disabled = true;

            const formData = new FormData(replyForm);
            
            fetch(replyForm.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => {
                if (res.ok || res.redirected) {
                    replyForm.reset();
                    document.getElementById('fileName').textContent = '';
                    // Langsung muat pesan baru
                    fetchNewMessages();
                } else {
                    alert('Gagal mengirim pesan.');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Terjadi kesalahan jaringan.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnHtml;
                submitBtn.disabled = false;
            });
        });
    }
    @endif
});
</script>
@endpush
