@extends('layouts.admin')
@section('title', 'Pengaturan Profil')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            {{-- Header --}}
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="bi bi-gear-fill text-primary fs-4"></i>
                </div>
                <div>
                    <h4 class="mb-0 fw-bold">Pengaturan Profil</h4>
                    <p class="text-muted mb-0 small">Kelola email dan keamanan akun Anda</p>
                </div>
            </div>

            {{-- Info Akun --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-person-circle text-primary me-2"></i>Informasi Akun</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Nama</label>
                            <p class="fw-medium mb-0">{{ auth()->user()->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Username</label>
                            <p class="fw-medium mb-0">{{ auth()->user()->username }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Role</label>
                            <p class="fw-medium mb-0">
                                @foreach(auth()->user()->getRoleNames() as $role)
                                    <span class="badge bg-primary bg-opacity-10 text-primary">{{ $role }}</span>
                                @endforeach
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small mb-1">Status</label>
                            <p class="mb-0">
                                <span class="badge bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Ubah Email --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-envelope-fill text-info me-2"></i>Ubah Email</h6>
                    <div id="emailSuccessAlert" class="alert alert-success py-2 px-3 d-none" style="font-size:0.85rem; border-radius:12px;">
                        <i class="bi bi-check-circle-fill me-1"></i><span></span>
                    </div>
                    <div id="emailErrorAlert" class="alert alert-danger py-2 px-3 d-none" style="font-size:0.85rem; border-radius:12px;"></div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="current_email">Email Saat Ini</label>
                            <input type="email" id="current_email" class="form-control" value="{{ auth()->user()->email ?? '(Belum diisi)' }}" disabled style="border-radius:12px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium" for="new_email">Email Baru</label>
                            <input type="email" id="new_email" class="form-control" placeholder="Masukkan email baru" style="border-radius:12px;">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-info text-white fw-semibold" id="btnUpdateEmail" style="border-radius:12px; padding:8px 24px;">
                            <i class="bi bi-save me-2"></i>Simpan Email
                        </button>
                    </div>
                </div>
            </div>

            {{-- Ubah Password --}}
            <div class="card border-0 shadow-sm mb-4" style="border-radius:16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3"><i class="bi bi-shield-lock-fill text-warning me-2"></i>Ubah Password</h6>
                    <div id="pwdSuccessAlert" class="alert alert-success py-2 px-3 d-none" style="font-size:0.85rem; border-radius:12px;">
                        <i class="bi bi-check-circle-fill me-1"></i><span></span>
                    </div>
                    <div id="pwdErrorAlert" class="alert alert-danger py-2 px-3 d-none" style="font-size:0.85rem; border-radius:12px;"></div>
                    <p class="text-muted small mb-3" style="line-height:1.6;">
                        Password baru harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol khusus (@$!%*#?&). 
                        Setelah berhasil mengubah password, Anda akan otomatis logout.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium" for="old_password">Password Lama</label>
                            <input type="password" id="old_password" class="form-control" placeholder="••••••••" style="border-radius:12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium" for="new_password">Password Baru</label>
                            <input type="password" id="new_password" class="form-control" placeholder="••••••••" style="border-radius:12px;">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium" for="new_password_confirmation">Konfirmasi</label>
                            <input type="password" id="new_password_confirmation" class="form-control" placeholder="••••••••" style="border-radius:12px;">
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="button" class="btn btn-warning text-dark fw-semibold" id="btnUpdatePassword" style="border-radius:12px; padding:8px 24px;">
                            <i class="bi bi-key-fill me-2"></i>Ubah Password & Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// === Update Email ===
document.getElementById('btnUpdateEmail').addEventListener('click', function() {
    const email = document.getElementById('new_email').value.trim();
    const btn = this;
    const successEl = document.getElementById('emailSuccessAlert');
    const errorEl = document.getElementById('emailErrorAlert');
    
    successEl.classList.add('d-none');
    errorEl.classList.add('d-none');

    if (!email || !email.includes('@')) {
        errorEl.textContent = 'Masukkan alamat email yang valid.';
        errorEl.classList.remove('d-none');
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    fetch('/api/v1/profile/email', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ email: email })
    })
    .then(r => r.json().then(data => ({status: r.status, body: data})))
    .then(result => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Email';

        if (result.status === 200 && result.body.success) {
            successEl.querySelector('span').textContent = result.body.message;
            successEl.classList.remove('d-none');
            document.getElementById('current_email').value = result.body.email;
            document.getElementById('new_email').value = '';
        } else if (result.status === 422) {
            let errors = result.body.errors;
            let msgs = [];
            for (let f in errors) errors[f].forEach(m => msgs.push(m));
            errorEl.textContent = msgs.join(', ');
            errorEl.classList.remove('d-none');
        } else {
            errorEl.textContent = result.body.message || 'Terjadi kesalahan.';
            errorEl.classList.remove('d-none');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-2"></i>Simpan Email';
        errorEl.textContent = 'Gagal menghubungi server.';
        errorEl.classList.remove('d-none');
    });
});

// === Update Password ===
document.getElementById('btnUpdatePassword').addEventListener('click', function() {
    const btn = this;
    const successEl = document.getElementById('pwdSuccessAlert');
    const errorEl = document.getElementById('pwdErrorAlert');
    
    successEl.classList.add('d-none');
    errorEl.classList.add('d-none');

    const formData = new FormData();
    formData.append('old_password', document.getElementById('old_password').value);
    formData.append('new_password', document.getElementById('new_password').value);
    formData.append('new_password_confirmation', document.getElementById('new_password_confirmation').value);

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengubah...';

    fetch('{{ route("admin.profile.password") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: formData
    })
    .then(r => r.json().then(data => ({status: r.status, body: data})))
    .then(result => {
        if (result.status === 200 && result.body.success) {
            window.location.href = result.body.redirect;
        } else if (result.status === 422) {
            let errors = result.body.errors;
            let html = '<ul class="mb-0 ps-3">';
            for (let f in errors) errors[f].forEach(m => { html += `<li>${m}</li>`; });
            html += '</ul>';
            errorEl.innerHTML = html;
            errorEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-key-fill me-2"></i>Ubah Password & Logout';
        } else {
            errorEl.textContent = 'Terjadi kesalahan.';
            errorEl.classList.remove('d-none');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-key-fill me-2"></i>Ubah Password & Logout';
        }
    })
    .catch(() => {
        errorEl.textContent = 'Gagal menghubungi server.';
        errorEl.classList.remove('d-none');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-key-fill me-2"></i>Ubah Password & Logout';
    });
});
</script>
@endpush
