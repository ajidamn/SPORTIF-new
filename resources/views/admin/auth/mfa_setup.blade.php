<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup MFA - SPORTIF JATIM</title>
    <!-- Tambahkan CSS Tailwind jika ada, atau gunakan style bawaan login -->
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f3f4f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 400px; text-align: center; }
        .btn { background: #3b82f6; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; width: 100%; font-size: 16px; margin-top: 15px;}
        .btn:hover { background: #2563eb; }
        input { width: 100%; padding: 10px; margin-top: 10px; border: 1px solid #d1d5db; border-radius: 4px; box-sizing: border-box; text-align: center; letter-spacing: 5px; font-size: 18px;}
        .error { color: #ef4444; font-size: 14px; margin-top: 5px; }
        svg { margin: 0 auto; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Setup Keamanan Ganda (MFA)</h2>
        <p style="color: #6b7280; font-size: 14px;">Akun SuperAdmin wajib menggunakan perlindungan ekstra. Silakan *scan* QR Code di bawah menggunakan aplikasi <strong>Google Authenticator</strong> di HP Anda.</p>
        
        <div style="margin: 20px 0;">
            {!! $qrCodeUrl !!}
        </div>

        <form action="{{ route('admin.mfa.verify') }}" method="POST">
            @csrf
            <input type="hidden" name="secret" value="{{ $secret }}">
            
            <label for="otp" style="font-size: 14px; font-weight: bold;">Masukkan 6 Digit Kode OTP</label>
            <input type="text" name="one_time_password" id="otp" placeholder="123456" maxlength="6" required autocomplete="off">
            @error('one_time_password')
                <div class="error">{{ $message }}</div>
            @enderror

            <button type="submit" class="btn">Verifikasi & Aktifkan</button>
        </form>

        <form action="{{ route('admin.logout') }}" method="POST" style="margin-top: 10px;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; text-decoration: underline;">Batal & Logout</button>
        </form>
    </div>
</body>
</html>
