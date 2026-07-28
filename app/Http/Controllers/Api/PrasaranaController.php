<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FasilitasPrasarana;
use App\Models\FotoPrasarana;
use App\Models\LogSistem;
use App\Models\Prasarana;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PrasaranaController extends Controller
{
    // ── Index (admin & public) ───────────────────────────────

    public function index(Request $r)
    {
        $q = Prasarana::with(['lokasi', 'cabors', 'fasilitas', 'fotos']);
        if ($r->search)     $q->where('nama', 'like', "%{$r->search}%");
        if ($r->lokasi_id)  $q->where('lokasi_id', $r->lokasi_id);
        if ($r->pengelola)  $q->where('pengelola', $r->pengelola);
        if ($r->jenis_id)   $q->where('jenis_id', $r->jenis_id);
        if ($r->kategori)   $q->where('kategori', $r->kategori);
        if ($r->standar)    $q->where('standar', $r->standar);
        if ($r->cabor_id)   $q->whereHas('cabors', fn($x) => $x->where('cabors.id', $r->cabor_id));
        return response()->json(
            $r->has('all') ? $q->orderBy('nama')->get() : $q->latest()->paginate($r->per_page ?? 15)
        );
    }

    public function publicIndex(Request $r)
    {
        // ISO 27001: Input validation
        $r->validate([
            'jenis_id'  => 'nullable|integer|exists:jenis,id',
            'cabor_id'  => 'nullable|integer|exists:cabors,id',
            'lokasi_id' => 'nullable|integer|exists:kab_kota,id',
            'pengelola' => 'nullable|string|in:Pemerintah Kabupaten/Kota,Pemerintah Provinsi,Swasta,BUMN/BUMD,Kepolisian,Militer',
            'kategori'  => 'nullable|string',
            'standar'   => 'nullable|string|in:Belum di Standarisasi,Regional,Nasional,Internasional',
        ]);

        $q = Prasarana::with(['lokasi', 'cabors', 'fotos', 'jenis']);

        if ($r->jenis_id) {
            $q->where('jenis_id', $r->jenis_id);
        }
        if ($r->cabor_id) {
            $q->whereHas('cabors', fn($x) => $x->where('cabors.id', $r->cabor_id));
        }
        if ($r->lokasi_id) {
            $q->where('lokasi_id', $r->lokasi_id);
        }
        if ($r->pengelola) {
            $q->where('pengelola', $r->pengelola);
        }
        if ($r->kategori) {
            $q->where('kategori', $r->kategori);
        }
        if ($r->standar) {
            $q->where('standar', $r->standar);
        }

        return response()->json($q->orderBy('nama')->get());
    }

    // ── Show ─────────────────────────────────────────────────

    public function show(Prasarana $prasarana)
    {
        return response()->json(
            $prasarana->load(['lokasi', 'cabors', 'fasilitas', 'fotos', 'jenis'])
        );
    }

    // ── Store ────────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $this->validatePrasarana($request);

        // Security: IDOR Protection
        if (auth()->user() && auth()->user()->kab_kota_id) {
            $data['lokasi_id'] = auth()->user()->kab_kota_id;
        }

        DB::beginTransaction();
        try {
            $prasarana = Prasarana::create($data);

            // Multi-cabor
            if (!empty($request->cabor_ids)) {
                $prasarana->cabors()->sync($request->cabor_ids);
            }

            // Fasilitas (multi-row: nama, jumlah, kondisi, keterangan)
            if ($request->fasilitas && is_array($request->fasilitas)) {
                $this->syncFasilitas($prasarana, $request->fasilitas);
            }

            LogSistem::catat('CREATE', 'Prasarana', "Menambah: {$prasarana->nama}");
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }

        return response()->json($prasarana->load(['lokasi', 'cabors', 'fasilitas']), 201);
    }

    // ── Update ───────────────────────────────────────────────

    public function update(Request $request, Prasarana $prasarana)
    {
        $data = $this->validatePrasarana($request);

        // Security: IDOR Protection
        if (auth()->user() && auth()->user()->kab_kota_id) {
            $data['lokasi_id'] = auth()->user()->kab_kota_id;
        }

        DB::beginTransaction();
        try {
            $oldData = $prasarana->toArray();
            $prasarana->update($data);

            if ($request->has('cabor_ids')) {
                $prasarana->cabors()->sync($request->cabor_ids ?? []);
            }

            if ($request->fasilitas && is_array($request->fasilitas)) {
                $this->syncFasilitas($prasarana, $request->fasilitas);
            }

            LogSistem::catat('UPDATE', 'Prasarana', "Mengubah: {$prasarana->nama}", $oldData, $prasarana->toArray());
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }

        return response()->json($prasarana->load(['lokasi', 'cabors', 'fasilitas']));
    }

    // ── Destroy ──────────────────────────────────────────────

    public function destroy(Prasarana $prasarana)
    {
        $oldData = $prasarana->toArray();
        LogSistem::catat('DELETE', 'Prasarana', "Menghapus: {$prasarana->nama}", $oldData);
        $prasarana->delete();
        return response()->json(['message' => 'Prasarana berhasil dihapus']);
    }

    // ── Fasilitas Sub-routes ─────────────────────────────────

    public function storeFasilitas(Request $request, Prasarana $prasarana)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'jumlah'     => 'nullable|integer|min:1',
            'kondisi'    => 'nullable|in:Baik,Rusak Ringan,Rusak Berat',
            'keterangan' => 'nullable|string',
        ]);

        $fasilitas = $prasarana->fasilitas()->create($request->only('nama', 'jumlah', 'kondisi', 'keterangan'));
        return response()->json($fasilitas, 201);
    }

    public function updateFasilitas(Request $request, Prasarana $prasarana, FasilitasPrasarana $fasilitas)
    {
        $request->validate([
            'nama'       => 'required|string|max:255',
            'jumlah'     => 'nullable|integer|min:1',
            'kondisi'    => 'nullable|in:Baik,Rusak Ringan,Rusak Berat',
            'keterangan' => 'nullable|string',
        ]);
        $fasilitas->update($request->only('nama', 'jumlah', 'kondisi', 'keterangan'));
        return response()->json($fasilitas);
    }

    public function destroyFasilitas(Prasarana $prasarana, FasilitasPrasarana $fasilitas)
    {
        $fasilitas->delete();
        return response()->json(['message' => 'Fasilitas dihapus']);
    }

    // ── Foto Multi-upload ────────────────────────────────────

    public function uploadFoto(Request $request, Prasarana $prasarana)
    {
        $request->validate([
            'fotos'    => 'required|array',
            'fotos.*'  => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Maks 2MB
        ]);

        $saved = [];
        $manager = new ImageManager(new Driver());

        foreach ($request->file('fotos') as $foto) {
            $filename = Str::uuid() . '.webp';
            $image = $manager->read($foto->getRealPath());
            $encoded = $image->toWebp(80);
            
            $path = "prasarana/{$prasarana->id}/fotos/" . $filename;
            Storage::disk('public')->put($path, $encoded->toString());

            $saved[] = $prasarana->fotos()->create([
                'foto'      => $path,
                'deskripsi' => $request->deskripsi ?? null,
            ]);
        }

        return response()->json(['message' => count($saved) . ' foto berhasil diupload', 'data' => $saved], 201);
    }

    public function destroyFoto(FotoPrasarana $foto)
    {
        Storage::disk('public')->delete($foto->foto);
        $foto->delete();
        return response()->json(['message' => 'Foto dihapus']);
    }

    // ── Private Helpers ──────────────────────────────────────

    private function validatePrasarana(Request $r): array
    {
        return $r->validate([
            'nama'            => 'required|string|max:255',
            'jenis_id'        => 'nullable|exists:jenis,id',
            'lokasi_id'       => 'nullable|exists:kab_kota,id',
            'alamat'          => 'nullable|string',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'pengelola'       => 'nullable|string|in:Pemerintah Kabupaten/Kota,Pemerintah Provinsi,Swasta,BUMN/BUMD,Kepolisian,Militer',
            'kategori'        => 'nullable|string',
            'standar'         => 'nullable|string|in:Belum di Standarisasi,Regional,Nasional,Internasional',
            'kapasitas'       => 'nullable|integer',
            'narahubung'      => 'nullable|string|max:255',
            'telp_narahubung' => 'nullable|string|max:20',
            'keterangan'      => 'nullable|string',
        ]);
    }

    /**
     * Sync fasilitas: hapus yang tidak ada di request, update yang ada, buat baru.
     */
    private function syncFasilitas(Prasarana $prasarana, array $fasilitasList): void
    {
        $incomingIds = collect($fasilitasList)->pluck('id')->filter()->toArray();

        // Hapus fasilitas lama yang tidak ada di daftar baru
        $prasarana->fasilitas()->whereNotIn('id', $incomingIds)->delete();

        foreach ($fasilitasList as $item) {
            $payload = [
                'nama'       => $item['nama'],
                'jumlah'     => $item['jumlah'] ?? 1,
                'kondisi'    => $item['kondisi'] ?? 'Baik',
                'keterangan' => $item['keterangan'] ?? null,
            ];

            if (!empty($item['id'])) {
                FasilitasPrasarana::where('id', $item['id'])
                    ->where('prasarana_id', $prasarana->id)
                    ->update($payload);
            } else {
                $prasarana->fasilitas()->create($payload);
            }
        }
    }
}
