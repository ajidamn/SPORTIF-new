<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SPORTIF Admin</title>
    <meta name="description" content="Panel Admin Sistem Informasi SPORTIF Dispora Jawa Timur">
    <link rel="icon" type="image/png" href="{{ asset('logo/4_sportif.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --tech-blue: #00d4ff;
            --tech-blue-dim: #0088aa;
            --emerald: #00ff88;
            --dark-bg: #030712;
            --dark-surface: #0a0f1e;
            --dark-card: rgba(10, 15, 35, 0.7);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-bg: rgba(255, 255, 255, 0.03);
            --text-primary: #e2e8f0;
            --text-secondary: rgba(226, 232, 240, 0.6);
            --font-body: 'Inter', sans-serif;
            --font-display: 'Outfit', sans-serif;
        }
        
        body {
            font-family: var(--font-body);
            background: var(--dark-bg);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background:
                radial-gradient(ellipse 80% 50% at 50% 0%, rgba(0,212,255,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 20% 80%, rgba(0,255,136,0.05) 0%, transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 20px;
            z-index: 1;
            position: relative;
        }

        .login-card {
            background: var(--dark-card);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 32px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.4), 0 0 60px rgba(0, 212, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--tech-blue), var(--emerald), transparent);
            opacity: 0.7;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .login-logo img {
            height: 55px;
            width: auto;
            filter: drop-shadow(0 0 10px rgba(255,255,255,0.2));
        }

        .login-title {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-sub {
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.85rem;
            margin-bottom: 32px;
            line-height: 1.5;
        }

        .form-label {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.85rem;
            margin-bottom: 6px;
        }

        .input-group-text {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-secondary);
            border-radius: 12px 0 0 12px;
            border-right: none;
        }

        .form-control {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            border-radius: 0 12px 12px 0;
            border-left: none;
            padding: 10px 16px 10px 0;
            font-size: 0.95rem;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(0, 212, 255, 0.4);
            color: var(--text-primary);
            box-shadow: inset 0 1px 1px rgba(0,0,0,0.075), 0 0 8px rgba(0, 212, 255, 0.2);
        }

        .form-control::placeholder {
            color: rgba(226, 232, 240, 0.3);
        }

        .form-check-input {
            background-color: var(--glass-bg);
            border-color: var(--glass-border);
        }

        .form-check-input:checked {
            background-color: var(--tech-blue);
            border-color: var(--tech-blue);
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.5);
        }

        .form-check-label {
            color: var(--text-secondary);
        }

        .btn-login {
            background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
            color: var(--dark-bg);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            padding: 12px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 212, 255, 0.3);
            color: var(--dark-bg);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .footer-text {
            color: rgba(226, 232, 240, 0.4);
            font-size: 0.75rem;
        }

        .forgot-link {
            color: var(--tech-blue);
            font-size: 0.82rem;
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-link:hover {
            color: var(--emerald);
            text-decoration: underline;
        }

        /* Modal styling */
        .modal-content {
            background: var(--dark-card);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            color: var(--text-primary);
        }
        .modal-header {
            border-bottom: 1px solid var(--glass-border);
        }
        .modal-footer {
            border-top: 1px solid var(--glass-border);
        }
        .modal-title {
            font-family: var(--font-display);
            font-weight: 700;
            background: linear-gradient(135deg, var(--tech-blue), var(--emerald));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .modal .form-control {
            border-radius: 12px;
            border: 1px solid var(--glass-border);
            border-left: 1px solid var(--glass-border);
        }
        .modal .form-label {
            color: var(--text-primary);
            font-weight: 500;
            font-size: 0.85rem;
        }
        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .btn-forgot {
            background: linear-gradient(135deg, #f59e0b, #ef4444);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            padding: 10px;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-forgot:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 158, 11, 0.3);
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-logo"><img src="{{ asset('logo/4_sportif.png') }}" alt="SPORTIF"></div>
            <!-- <h1 class="login-title">SPORTIF</h1> -->
            <p class="login-sub">Sistem Pengelolaan Data Keolahragaan, Kepemudaan<br>& Kepramukaan Provinsi Jawa Timur</p>

            @if ($errors->any() && !$errors->has('forgot_username'))
            <div class="alert alert-danger py-2 px-3 rounded-3 mb-4 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ $errors->first() }}
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success py-2 px-3 rounded-3 mb-4 d-flex align-items-center gap-2" style="font-size:0.85rem; background: rgba(0, 255, 136, 0.1); border: 1px solid rgba(0, 255, 136, 0.2); color: var(--emerald);">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
            @endif

            @if(session('forgot_success'))
            <div class="alert py-2 px-3 rounded-3 mb-4 d-flex align-items-center gap-2" style="font-size:0.85rem; background: rgba(0, 212, 255, 0.1); border: 1px solid rgba(0, 212, 255, 0.2); color: var(--tech-blue);">
                <i class="bi bi-info-circle-fill"></i>
                {{ session('forgot_success') }}
            </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label" for="login_id">Username atau Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-circle"></i></span>
                        <input type="text" name="login_id" id="login_id" class="form-control" placeholder="Masukkan username atau email" value="{{ old('login_id') }}" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input type="checkbox" name="remember" id="remember" class="form-check-input">
                        <label for="remember" class="form-check-label small">Ingat saya</label>
                    </div>
                    <a href="#" class="forgot-link" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                        <i class="bi bi-question-circle me-1"></i>Lupa Password?
                    </a>
                </div>
                <button type="submit" class="btn btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Admin
                </button>
            </form>

            <div class="text-center mt-5 footer-text">
                &copy; {{ date('Y') }} DISPORA Provinsi Jawa Timur
            </div>
        </div>
    </div>

    {{-- Modal Lupa Password --}}
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="forgotPasswordLabel">
                        <i class="bi bi-shield-lock me-2"></i>Lupa Password
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.forgot-password') }}">
                    @csrf
                    <div class="modal-body">
                        <p style="color: var(--text-secondary); font-size: 0.85rem; line-height: 1.6;">
                            Permintaan reset password akan dikirim sebagai tiket aduan. 
                            Mohon sertakan <strong>No. WhatsApp Anda</strong> agar Admin dapat mengirimkan password baru.
                        </p>

                        @if($errors->has('forgot_username'))
                        <div class="alert alert-danger py-2 px-3 rounded-3 mb-3 d-flex align-items-center gap-2" style="font-size:0.85rem;">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            {{ $errors->first('forgot_username') }}
                        </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label" for="forgot_username">Username</label>
                            <input type="text" name="username" id="forgot_username" class="form-control" 
                                   placeholder="Masukkan username Anda" value="{{ old('username') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="forgot_no_wa">No. WhatsApp Anda</label>
                            <input type="text" name="no_wa" id="forgot_no_wa" class="form-control" 
                                   placeholder="Contoh: 08123456789" value="{{ old('no_wa') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="forgot_deskripsi">Deskripsi Masalah</label>
                            <textarea name="deskripsi" id="forgot_deskripsi" class="form-control" rows="2" 
                                      placeholder="Jelaskan permasalahan Anda..." required>{{ old('deskripsi', 'Saya lupa password dan memohon untuk dilakukan reset password.') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-forgot">
                            <i class="bi bi-send-fill me-2"></i>Kirim Permintaan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @if($errors->has('forgot_username'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
        });
    </script>
    @endif
</body>
</html>
