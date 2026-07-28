<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MfaController extends Controller
{
    public function setup()
    {
        $user = Auth::user();
        if ($user->google2fa_secret) {
            return redirect()->route('admin.dashboard');
        }

        $google2fa = app('pragmarx.google2fa');
        $secret = $google2fa->generateSecretKey();

        $qrCodeUrl = $google2fa->getQRCodeInline(
            config('app.name'),
            $user->email,
            $secret
        );

        return view('admin.auth.mfa_setup', compact('qrCodeUrl', 'secret'));
    }

    public function verifySetup(Request $request)
    {
        $request->validate([
            'secret' => 'required',
            'one_time_password' => 'required|digits:6',
        ]);

        $google2fa = app('pragmarx.google2fa');
        $valid = $google2fa->verifyKey($request->secret, $request->one_time_password);

        if ($valid) {
            $user = Auth::user();
            $user->google2fa_secret = $request->secret;
            $user->save();

            return redirect()->route('admin.dashboard')->with('success', 'MFA Berhasil diaktifkan!');
        }

        return back()->withErrors(['one_time_password' => 'Kode OTP salah. Silakan coba lagi.']);
    }
}
