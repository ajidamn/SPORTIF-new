<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{User, LogSistem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $r) 
    { 
        $q = User::with(['roles', 'kabKota', 'cabor', 'jenis']);
        
        if ($r->search) {
            $q->where(function($query) use ($r) {
                $query->where('name', 'like', "%{$r->search}%")
                      ->orWhere('email', 'like', "%{$r->search}%")
                      ->orWhere('username', 'like', "%{$r->search}%");
            });
        }
        
        if ($r->role) {
            $q->whereHas('roles', function($query) use ($r) {
                $query->where('name', $r->role);
            });
        }

        if ($r->kab_kota_id) $q->where('kab_kota_id', $r->kab_kota_id);
        if ($r->cabor_id)    $q->where('cabor_id', $r->cabor_id);
        if ($r->jenis_id)    $q->where('jenis_id', $r->jenis_id);
        if ($r->filled('status')) $q->where('is_active', $r->status);

        return response()->json($q->latest()->paginate($r->per_page ?? 15)); 
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'name' => 'required',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'role' => 'required',
            'kab_kota_id' => 'nullable|exists:kab_kota,id',
            'cabor_id' => 'nullable|exists:cabor,id',
            'jenis_id' => 'nullable|exists:jenis,id',
            'is_active' => 'nullable|boolean'
        ]);

        $u = User::create([
            'name' => $d['name'],
            'username' => $d['username'],
            'email' => $d['email'],
            'password' => Hash::make($d['password']),
            'kab_kota_id' => $d['kab_kota_id'] ?? null,
            'cabor_id' => $d['cabor_id'] ?? null,
            'jenis_id' => $d['jenis_id'] ?? null,
            'is_active' => $r->boolean('is_active', true)
        ]);
        
        $u->syncRoles([$d['role']]);
        LogSistem::catat('CREATE', 'User', "Menambah user: {$u->name} ({$d['role']})");
        return response()->json($u->load('roles', 'kabKota', 'cabor', 'jenis'), 201);
    }

    public function show(User $user) 
    { 
        return response()->json($user->load('roles', 'kabKota', 'cabor', 'jenis')); 
    }

    public function update(Request $r, User $user)
    {
        $d = $r->validate([
            'name' => 'required',
            'username' => "required|string|unique:users,username,{$user->id}",
            'email' => "required|email|unique:users,email,{$user->id}",
            'password' => ['nullable', Password::min(8)->letters()->numbers()->symbols()],
            'role' => 'required',
            'kab_kota_id' => 'nullable|exists:kab_kota,id',
            'cabor_id' => 'nullable|exists:cabor,id',
            'jenis_id' => 'nullable|exists:jenis,id',
            'is_active' => 'nullable|boolean'
        ]);

        $user->update([
            'name' => $d['name'],
            'username' => $d['username'],
            'email' => $d['email'],
            'kab_kota_id' => $d['kab_kota_id'] ?? null,
            'cabor_id' => $d['cabor_id'] ?? null,
            'jenis_id' => $d['jenis_id'] ?? null,
            'is_active' => $r->boolean('is_active', true)
        ]);

        if ($r->filled('password')) {
            $user->update(['password' => Hash::make($r->password)]);
        }
        
        $user->syncRoles([$d['role']]);
        LogSistem::catat('UPDATE', 'User', "Update user: {$user->name}");
        return response()->json($user->load('roles', 'kabKota', 'cabor', 'jenis'));
    }

    public function destroy(User $user) 
    { 
        LogSistem::catat('DELETE', 'User', "Menghapus user: {$user->name}"); 
        $user->delete(); 
        return response()->json(['message' => 'Deleted']); 
    }
}
