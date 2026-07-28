<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Operator, LogSistem, Skala};
use Illuminate\Http\Request;

class OperatorController extends Controller
{
    public function index(Request $r)
    {
        $q = Operator::with(['skala', 'cabor', 'kabKota', 'role', 'user']);

        if ($r->search) {
            $q->where(function ($query) use ($r) {
                $query->where('nama', 'like', "%{$r->search}%")
                      ->orWhere('nik', 'like', "%{$r->search}%")
                      ->orWhere('nip', 'like', "%{$r->search}%")
                      ->orWhere('email', 'like', "%{$r->search}%")
                      ->orWhere('jabatan', 'like', "%{$r->search}%");
            });
        }

        if ($r->skala_id)    $q->where('skala_id', $r->skala_id);
        if ($r->kabkota_id)  $q->where('kabkota_id', $r->kabkota_id);
        if ($r->cabor_id)    $q->where('cabor_id', $r->cabor_id);
        if ($r->role_id)     $q->where('role_id', $r->role_id);

        return response()->json($q->latest()->paginate($r->per_page ?? 15));
    }

    public function store(Request $r)
    {
        // Cari ID skala "Daerah" untuk validasi conditional
        $skalaDaerahId = Skala::where('nama', 'Daerah')->value('id');

        $d = $r->validate([
            'nik'        => 'required|digits:16|unique:operators',
            'nama'       => 'required|string|max:255',
            'role_id'    => 'required|exists:roles,id',
            'skala_id'   => 'required|exists:skala,id',
            'cabor_id'   => 'nullable|exists:cabors,id',
            'nip'        => 'nullable|string|max:18',
            'jabatan'    => 'required|string|max:255',
            'email'      => 'nullable|email',
            'no_telp'    => 'nullable|string|max:20',
            'kabkota_id' => ($r->skala_id == $skalaDaerahId ? 'required' : 'nullable') . '|exists:kab_kota,id',
            'user_id'    => 'nullable|exists:users,id',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits'   => 'NIK harus 16 digit angka.',
            'nik.unique'   => 'NIK sudah terdaftar.',
            'kabkota_id.required' => 'Kab/Kota wajib diisi jika skala Daerah.',
        ]);

        $operator = Operator::create($d);
        LogSistem::catat('CREATE', 'Operator', "Menambah operator: {$operator->nama} (NIK: {$operator->nik})");

        return response()->json($operator->load(['skala', 'cabor', 'kabKota', 'role', 'user']), 201);
    }

    public function show(Operator $operator)
    {
        return response()->json($operator->load(['skala', 'cabor', 'kabKota', 'role', 'user']));
    }

    public function update(Request $r, Operator $operator)
    {
        $skalaDaerahId = Skala::where('nama', 'Daerah')->value('id');

        $d = $r->validate([
            'nik'        => "required|digits:16|unique:operators,nik,{$operator->id}",
            'nama'       => 'required|string|max:255',
            'role_id'    => 'required|exists:roles,id',
            'skala_id'   => 'required|exists:skala,id',
            'cabor_id'   => 'nullable|exists:cabors,id',
            'nip'        => 'nullable|string|max:18',
            'jabatan'    => 'required|string|max:255',
            'email'      => 'nullable|email',
            'no_telp'    => 'nullable|string|max:20',
            'kabkota_id' => ($r->skala_id == $skalaDaerahId ? 'required' : 'nullable') . '|exists:kab_kota,id',
            'user_id'    => 'nullable|exists:users,id',
        ], [
            'nik.required' => 'NIK wajib diisi.',
            'nik.digits'   => 'NIK harus 16 digit angka.',
            'nik.unique'   => 'NIK sudah terdaftar.',
            'kabkota_id.required' => 'Kab/Kota wajib diisi jika skala Daerah.',
        ]);

        $operator->update($d);
        LogSistem::catat('UPDATE', 'Operator', "Update operator: {$operator->nama} (NIK: {$operator->nik})");

        return response()->json($operator->load(['skala', 'cabor', 'kabKota', 'role', 'user']));
    }

    public function destroy(Operator $operator)
    {
        LogSistem::catat('DELETE', 'Operator', "Menghapus operator: {$operator->nama} (NIK: {$operator->nik})");
        $operator->delete();
        return response()->json(['message' => 'Operator berhasil dihapus.']);
    }
}
