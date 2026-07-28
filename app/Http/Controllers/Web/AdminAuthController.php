<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Notification;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string',
            'password'  => 'required',
        ]);

        $loginId = $request->input('login_id');
        $fieldType = filter_var($loginId, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $credentials = [$fieldType => $loginId, 'password' => $request->password];

        $throttleKey = strtolower($loginId) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            
            // Kirim notifikasi peringatan keamanan
            $superAdmins = User::role('SuperAdmin')->get();
            Notification::send($superAdmins, new SecurityAlertNotification("Percobaan masuk mencurigakan terdeteksi (Gagal 5x). {$fieldType}: {$loginId}, IP: {$request->ip()}"));

            throw ValidationException::withMessages([
                'login_id' => "Terlalu banyak percobaan login. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            
            // R8: Single-Session Enforcement
            Auth::logoutOtherDevices($request->password);
            
            \App\Models\LogSistem::catat('LOGIN', 'Auth', "User berhasil login via {$fieldType}");

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors(['login_id' => 'Username/Email atau password salah.'])->onlyInput('login_id');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            \App\Models\LogSistem::catat('LOGOUT', 'Auth', "User berhasil logout");
        }
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'old_password' => ['required'],
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
                'confirmed'
            ],
        ], [
            'new_password.regex' => 'Password baru harus mengandung setidaknya satu huruf besar, huruf kecil, angka, dan simbol khusus (@$!%*#?&).',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user = auth()->user();

        if (!Hash::check($request->old_password, $user->password)) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => ['old_password' => ['Password lama tidak sesuai.']]], 422);
            }
            return back()->withErrors(['old_password' => 'Password lama tidak sesuai.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        \App\Models\LogSistem::catat('UPDATE', 'User', "Pengguna {$user->name} mengubah kata sandi mereka");
        $user->notify(new SecurityAlertNotification("Kata sandi akun Anda baru saja diubah. Jika ini bukan Anda, segera hubungi SuperAdmin."));

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Menyimpan pesan sukses ke flash session login
        session()->flash('success', 'Password berhasil diubah. Silakan login kembali dengan password baru.');

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'redirect' => route('admin.login')
            ]);
        }

        return redirect()->route('admin.login');
    }

    /**
     * Lupa Password — buat tiket aduan kategori "Reset Password"
     * Route ini bisa diakses tanpa login (guest).
     */
    public function submitForgotPassword(Request $request)
    {
        $request->validate([
            'username'  => 'required|string',
            'no_wa'     => 'required|string',
            'deskripsi' => 'required|string|max:1000',
        ], [
            'username.required'  => 'Username wajib diisi.',
            'no_wa.required'     => 'Nomor WhatsApp wajib diisi.',
            'deskripsi.required' => 'Deskripsi masalah wajib diisi.',
        ]);

        $user = User::where('username', $request->username)->where('is_active', true)->first();

        if (!$user) {
            return back()->withErrors(['forgot_username' => 'Username tidak ditemukan atau akun tidak aktif.'])->withInput();
        }

        $fullDeskripsi = "Nomor WA untuk dihubungi: " . $request->no_wa . "\n\nDetail Masalah:\n" . $request->deskripsi;

        $ticket = \App\Models\Ticket::create([
            'kode_tiket' => 'TKT-' . date('ymd') . '-' . rand(1000, 9999),
            'user_id'    => $user->id,
            'judul'      => "Permintaan Reset Password — {$user->username}",
            'kategori'   => 'Reset Password',
            'deskripsi'  => $fullDeskripsi,
            'status'     => 'open',
        ]);

        // Kirim notifikasi ke SuperAdmin
        $superAdmins = User::role('SuperAdmin')->get();
        Notification::send($superAdmins, new \App\Notifications\TicketCreatedNotification($ticket));

        return back()->with('forgot_success', 'Permintaan reset password telah dikirim. Silakan tunggu konfirmasi dari Admin melalui layanan Aduan.');
    }
}
