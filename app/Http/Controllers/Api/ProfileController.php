<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use App\Models\User;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class ProfileController extends Controller
{
    /**
     * Update email user yang sedang login.
     */
    public function updateEmail(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email' => "required|email|unique:users,email,{$user->id}",
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh akun lain.',
        ]);

        $oldEmail = $user->email;
        $user->email = $request->email;
        $user->save();

        LogSistem::catat('UPDATE', 'User', "Pengguna {$user->name} mengubah email dari '{$oldEmail}' menjadi '{$request->email}'");

        // Kirim notifikasi keamanan ke SuperAdmin
        $superAdmins = User::role('SuperAdmin')->where('id', '!=', $user->id)->get();
        if ($superAdmins->isNotEmpty()) {
            Notification::send($superAdmins, new SecurityAlertNotification(
                "Pengguna {$user->name} ({$user->username}) mengubah email dari '{$oldEmail}' menjadi '{$request->email}'."
            ));
        }

        return response()->json([
            'success' => true,
            'message' => 'Email berhasil diperbarui.',
            'email' => $user->email,
        ]);
    }
}
