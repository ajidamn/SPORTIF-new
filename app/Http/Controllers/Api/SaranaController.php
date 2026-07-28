<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\{Sarana, LogSistem};
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;

class SaranaController extends Controller
{
    public function index(Request $r)
    {
        $q = Sarana::with(['kabKota', 'jenis', 'cabor', 'prasarana']);
        
        if ($r->search) {
            $q->where(function($query) use ($r) {
                $query->where('nama_barang', 'like', "%{$r->search}%")
                      ->orWhere('kode_inventaris', 'like', "%{$r->search}%");
            });
        }
        if ($r->jenis_id) $q->where('jenis_id', $r->jenis_id);
        if ($r->cabor_id) $q->where('cabor_id', $r->cabor_id);
        if ($r->kab_kota_id) $q->where('kab_kota_id', $r->kab_kota_id);
        if ($r->kondisi) $q->where('kondisi', $r->kondisi);
        if ($r->status) $q->where('status', $r->status);
        if ($r->posisi_aset) $q->where('posisi_aset', $r->posisi_aset);

        return response()->json($q->latest()->paginate($r->per_page ?? 15));
    }

    private function validateSarana(Request $r)
    {
        return $r->validate([
            'kab_kota_id' => 'nullable|integer|exists:kab_kota,id',
            'jenis_id' => 'nullable|integer|exists:jenis,id',
            'kode_inventaris' => 'nullable|string|max:100',
            'nama_barang' => 'required|string|max:255',
            'spesifikasi' => 'nullable|string',
            'kondisi' => 'nullable|string|in:baik,rusak_ringan,rusak_berat,butuh_perbaikan,dalam_perbaikan,tidak_layak',
            'status' => 'nullable|string|in:tersedia,dipakai,dipinjam,dipelihara,hilang,rusak_total,dijual,dimusnahkan',
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cabor_id' => 'nullable|integer|exists:cabors,id',
            'posisi_aset' => 'nullable|string|in:prasarana,internal_dinas',
            'lokasi_barang' => 'nullable|integer',
            'keterangan_lokasi' => 'nullable|string',
            'jumlah' => 'nullable|integer|min:1',
            'satuan' => 'nullable|string|max:50',
            'tahun_pengadaan' => 'nullable|integer|digits:4',
            'sumber_dana' => 'nullable|string|max:100',
        ]);
    }

    public function store(Request $r)
    {
        $v = $this->validateSarana($r);

        // Security: IDOR Protection (Prevent spoofing kab_kota_id)
        if (auth()->user() && auth()->user()->kab_kota_id) {
            $v['kab_kota_id'] = auth()->user()->kab_kota_id;
        }

        if ($r->hasFile('foto_barang')) {
            $manager = new ImageManager(new Driver());
            $filename = Str::uuid() . '.webp';
            $image = $manager->read($r->file('foto_barang')->getRealPath());
            $encoded = $image->toWebp(80);
            
            $path = 'sarana/fotos/' . $filename;
            Storage::disk('public')->put($path, $encoded->toString());
            $v['foto_barang'] = $path;
        }

        $s = Sarana::create($v);
        LogSistem::catat('CREATE', 'Sarana', "Menambah Sarana: {$s->nama_barang}");
        return response()->json($s, 201);
    }

    public function show(Sarana $sarana)
    {
        return response()->json($sarana->load(['kabKota', 'jenis', 'cabor', 'prasarana']));
    }

    public function update(Request $r, Sarana $sarana)
    {
        $v = $this->validateSarana($r);

        // Security: IDOR Protection
        if (auth()->user() && auth()->user()->kab_kota_id) {
            $v['kab_kota_id'] = auth()->user()->kab_kota_id;
        }

        if ($r->hasFile('foto_barang')) {
            if ($sarana->foto_barang) {
                Storage::disk('public')->delete($sarana->foto_barang);
            }
            
            $manager = new ImageManager(new Driver());
            $filename = Str::uuid() . '.webp';
            $image = $manager->read($r->file('foto_barang')->getRealPath());
            $encoded = $image->toWebp(80);
            
            $path = 'sarana/fotos/' . $filename;
            Storage::disk('public')->put($path, $encoded->toString());
            $v['foto_barang'] = $path;
        }

        $oldData = $sarana->toArray();
        $sarana->update($v);
        LogSistem::catat('UPDATE', 'Sarana', "Mengedit Sarana: {$sarana->nama_barang}", $oldData, $sarana->toArray());
        return response()->json($sarana);
    }

    public function destroy(Sarana $sarana)
    {
        if ($sarana->foto_barang) {
            Storage::disk('public')->delete($sarana->foto_barang);
        }
        $oldData = $sarana->toArray();
        $n = $sarana->nama_barang;
        $sarana->delete();
        LogSistem::catat('DELETE', 'Sarana', "Menghapus Sarana: {$n}", $oldData);
        return response()->json(['message' => 'Deleted']);
    }
}
