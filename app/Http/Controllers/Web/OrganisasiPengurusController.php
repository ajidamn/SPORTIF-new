<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{Organisasi, PengurusOrganisasi, LogSistem};
use Illuminate\Http\Request;

class OrganisasiPengurusController extends Controller
{
    public function index($id)
    {
        $organisasi = Organisasi::with(['jenis', 'kabKota'])->findOrFail($id);
        
        $user = auth()->user();
        $roles = $user->getRoleNames();
        $isReadOnly = $roles->contains(fn($r) => str_starts_with($r, 'Kepala') || str_starts_with($r, 'Ketua'));

        return view('admin.organisasi-pengurus', [
            'title' => 'Pengurus - ' . $organisasi->nama,
            'pageSlug' => 'organisasi.pengurus',
            'organisasi' => $organisasi,
            'isReadOnly' => $isReadOnly
        ]);
    }

    public function data($id)
    {
        $data = PengurusOrganisasi::with(['ketua', 'sekretaris', 'bendahara'])
            ->where('organisasi_id', $id)
            ->latest()
            ->get();
        return response()->json($data);
    }

    public function store(Request $request, $id)
    {
        $organisasi = Organisasi::findOrFail($id);
        $data = $request->validate([
            'ketua_id' => 'nullable|exists:orang,id',
            'sekretaris_id' => 'nullable|exists:orang,id',
            'bendahara_id' => 'nullable|exists:orang,id',
            'jumlah_anggota' => 'nullable|integer',
            'sk_kepengurusan' => 'required|string',
            'tgl_awal' => 'required|date',
            'tgl_akhir' => 'nullable|date|after_or_equal:tgl_awal',
        ]);

        $data['organisasi_id'] = $organisasi->id;
        
        if ($request->id) {
            $pengurus = PengurusOrganisasi::findOrFail($request->id);
            $pengurus->update($data);
            LogSistem::catat('UPDATE', 'PengurusOrganisasi', "Mengubah riwayat kepengurusan {$organisasi->nama}");
        } else {
            PengurusOrganisasi::create($data);
            LogSistem::catat('CREATE', 'PengurusOrganisasi', "Menambah riwayat kepengurusan {$organisasi->nama}");
        }

        return response()->json(['message' => 'Berhasil disimpan']);
    }

    public function destroy($id, $pengurus_id)
    {
        $pengurus = PengurusOrganisasi::findOrFail($pengurus_id);
        $pengurus->delete();
        LogSistem::catat('DELETE', 'PengurusOrganisasi', "Menghapus riwayat kepengurusan");
        return response()->json(['message' => 'Berhasil dihapus']);
    }
}
