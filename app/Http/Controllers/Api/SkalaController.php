<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use App\Models\Skala;
use Illuminate\Http\Request;

/**
 * SkalaController — CRUD Master Data Skala
 * ISO 27001: Input validation, audit logging, generic error messages.
 */
class SkalaController extends Controller
{
    public function index(Request $request)
    {
        $query = Skala::query();

        if ($request->search) {
            $query->where('nama', 'like', "%{$request->search}%");
        }

        return response()->json(
            $request->has('all') ? $query->orderBy('nama')->get() : $query->orderBy('id')->paginate($request->per_page ?? 15)
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:skala,nama',
        ], [
            'nama.required' => 'Nama skala wajib diisi.',
            'nama.unique'   => 'Skala dengan nama tersebut sudah ada.',
        ]);

        $skala = Skala::create($data);
        LogSistem::catat('CREATE', 'Skala', "Menambah skala: {$skala->nama}");

        return response()->json($skala, 201);
    }

    public function show(Skala $skala)
    {
        return response()->json($skala);
    }

    public function update(Request $request, Skala $skala)
    {
        $data = $request->validate([
            'nama' => "required|string|max:100|unique:skala,nama,{$skala->id}",
        ]);

        $old = $skala->nama;
        $skala->update($data);
        LogSistem::catat('UPDATE', 'Skala', "Mengubah skala: {$old} → {$skala->nama}");

        return response()->json($skala);
    }

    public function destroy(Skala $skala)
    {
        // ISO 27001: Cek apakah skala sedang digunakan oleh event
        if ($skala->events()->exists()) {
            return response()->json([
                'message' => 'Skala tidak dapat dihapus karena masih digunakan oleh event.',
            ], 422);
        }

        LogSistem::catat('DELETE', 'Skala', "Menghapus skala: {$skala->nama}");
        $skala->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
