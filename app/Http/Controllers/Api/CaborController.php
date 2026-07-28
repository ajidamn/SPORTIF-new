<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cabor;
use App\Models\LogSistem;
use Illuminate\Http\Request;

class CaborController extends Controller
{
    public function index(Request $request)
    {
        $query = Cabor::query();
        if ($request->search) {
            $query->where('nama', 'like', "%{$request->search}%");
        }
        return response()->json(
            $request->has('all') ? $query->orderBy('nama')->get() : $query->latest()->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengprov' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);
        $cabor = Cabor::create($data);
        LogSistem::catat('CREATE', 'Cabor', "Menambah cabor: {$cabor->nama}");
        return response()->json($cabor, 201);
    }

    public function show(Cabor $cabor) { return response()->json($cabor); }

    public function update(Request $request, Cabor $cabor)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'nama_pengprov' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);
        $cabor->update($data);
        LogSistem::catat('UPDATE', 'Cabor', "Mengubah cabor: {$cabor->nama}");
        return response()->json($cabor);
    }

    public function destroy(Cabor $cabor)
    {
        LogSistem::catat('DELETE', 'Cabor', "Menghapus cabor: {$cabor->nama}");
        $cabor->delete();
        return response()->json(['message' => 'Cabor berhasil dihapus']);
    }
}
