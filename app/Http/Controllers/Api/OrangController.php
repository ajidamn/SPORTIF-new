<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogSistem;
use App\Models\Orang;
use App\Models\OrangStatus;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class OrangController extends Controller
{
    public function index(Request $request)
    {
        $query = Orang::with(['domisili', 'statusList.jenis', 'statusList.peran', 'statusList.cabor']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('nik', 'like', "%{$request->search}%")
                  ->orWhere('sportif_id', 'like', "%{$request->search}%");
            });
        }
        if ($request->domisili_id) {
            $query->where('domisili_id', $request->domisili_id);
        }
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        if ($request->jenis_id) {
            $query->whereHas('statusList', fn($q) => $q->where('jenis_id', $request->jenis_id));
        }
        if ($request->peran_id) {
            $query->whereHas('statusList', fn($q) => $q->where('peran_id', $request->peran_id));
        }
        if ($request->cabor_id) {
            $query->whereHas('statusList', fn($q) => $q->where('cabor_id', $request->cabor_id));
        }

        $user = auth()->user();
        if ($user && $user->kab_kota_id) {
            $query->where('domisili_id', $user->kab_kota_id);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nik'         => 'nullable|string|max:16',
            'nama'        => 'required|string|max:255',
            'tgl_lahir'   => 'nullable|date',
            'telp'        => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            'gender'      => 'nullable|in:L,P',
            'disabilitas'        => 'nullable|boolean',
            'jenis_disabilitas'  => 'nullable|in:fisik,intelektual,mental,sensorik_netra,sensorik_rungu,ganda',
            'tinggi'      => 'nullable|numeric',
            'berat'       => 'nullable|numeric',
            'gol_darah'   => 'nullable|in:A,B,AB,O',
            'domisili_id' => 'nullable|exists:kab_kota,id',
            'foto'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle File Upload & WebP Conversion
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = Str::uuid() . '.webp';
            $path = storage_path('app/public/fotos/orang');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image->toWebp(80)->save($path . '/' . $filename);
            
            $data['foto'] = 'fotos/orang/' . $filename;
        }

        // Security: IDOR Protection
        if (auth()->user() && auth()->user()->kab_kota_id) {
            $data['domisili_id'] = auth()->user()->kab_kota_id;
        }

        $orang = Orang::create($data);
        LogSistem::catat('CREATE', 'Orang', "Menambah data orang: {$orang->nama}", null, $orang->toArray());

        return response()->json($orang->load('domisili'), 201);
    }

    public function show(Orang $orang)
    {
        return response()->json($orang->load([
            'domisili',
            'statusList.jenis', 'statusList.peran', 'statusList.cabor', 'statusList.skala',
            'riwayatEvent.event', 'riwayatEvent.cabor',
        ]));
    }

    public function update(Request $request, Orang $orang)
    {
        $old = $orang->toArray();
        $data = $request->validate([
            'nik'         => 'nullable|string|max:16',
            'nama'        => 'required|string|max:255',
            'tgl_lahir'   => 'nullable|date',
            'telp'        => 'nullable|string|max:20',
            'alamat'      => 'nullable|string',
            'gender'      => 'nullable|in:L,P',
            'disabilitas'        => 'nullable|boolean',
            'jenis_disabilitas'  => 'nullable|in:fisik,intelektual,mental,sensorik_netra,sensorik_rungu,ganda',
            'tinggi'      => 'nullable|numeric',
            'berat'       => 'nullable|numeric',
            'gol_darah'   => 'nullable|in:A,B,AB,O',
            'domisili_id' => 'nullable|exists:kab_kota,id',
            'foto'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        // Handle File Upload & WebP Conversion
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = Str::uuid() . '.webp';
            $path = storage_path('app/public/fotos/orang');
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            $image->toWebp(80)->save($path . '/' . $filename);
            
            $data['foto'] = 'fotos/orang/' . $filename;

            // Hapus foto lama jika ada
            if ($orang->foto && Storage::disk('public')->exists($orang->foto)) {
                Storage::disk('public')->delete($orang->foto);
            }
        }

        // Security: IDOR Protection
        if (auth()->user() && auth()->user()->kab_kota_id) {
            $data['domisili_id'] = auth()->user()->kab_kota_id;
        }

        $orang->update($data);
        LogSistem::catat('UPDATE', 'Orang', "Mengubah data orang: {$orang->nama}", $old, $orang->toArray());

        return response()->json($orang->load('domisili'));
    }

    public function destroy(Orang $orang)
    {
        LogSistem::catat('DELETE', 'Orang', "Menghapus data orang: {$orang->nama}", $orang->toArray());
        $orang->delete();
        return response()->json(['message' => 'Data berhasil dihapus']);
    }

    // ══════════════════════════════════════════════════════════
    //  PUBLIC API — ISO 27001: Data Minimization
    // ══════════════════════════════════════════════════════════

    /**
     * Public listing — mask NIK, exclude alamat & telp.
     */
    public function publicIndex(Request $request)
    {
        // ISO 27001: Input validation
        $request->validate([
            'search'      => 'nullable|string|max:100',
            'gender'      => 'nullable|in:L,P',
            'jenis_id'    => 'nullable|integer|exists:jenis,id',
            'peran_id'    => 'nullable|integer|exists:peran,id',
            'cabor_id'    => 'nullable|integer|exists:cabors,id',
            'domisili_id' => 'nullable|integer|exists:kab_kota,id',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = Orang::with(['domisili', 'statusList.peran', 'statusList.cabor', 'statusList.jenis'])
            ->withCount('riwayatEvent')
            ->where('is_active', true);

        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('sportif_id', 'like', "%{$search}%");
                // ISO 27001: Tidak boleh search by NIK lengkap di public
            });
        }
        if ($request->gender) {
            $query->where('gender', $request->gender);
        }
        if ($request->jenis_id) {
            $query->whereHas('statusList', fn($q) => $q->where('jenis_id', $request->jenis_id));
        }
        if ($request->peran_id) {
            $query->whereHas('statusList', fn($q) => $q->where('peran_id', $request->peran_id));
        }
        if ($request->cabor_id) {
            $query->whereHas('statusList', fn($q) => $q->where('cabor_id', $request->cabor_id));
        }
        if ($request->domisili_id) {
            $query->where('domisili_id', $request->domisili_id);
        }

        $cacheKey = 'public_orang_index_' . md5(json_encode($request->all()));

        $data = Cache::remember($cacheKey, 600, function() use ($query, $request) {
            $paginator = $query->orderBy('nama')->paginate($request->per_page ?? 20);
            
            // ISO 27001 + UU ITE: Data minimization
            $paginator->getCollection()->transform(function($o) {
                $o->riwayat_count = $o->riwayat_event_count ?? 0;
                $o->nik = $this->maskNik($o->nik);
                $o->append('umur');
                $o->makeHidden(['telp', 'alamat', 'tinggi', 'berat', 'tgl_lahir']);
                return $o;
            });
            
            return $paginator;
        });

        return response()->json($data);
    }

    /**
     * Public summary — count per peran (Atlet, Pelatih, Wasit/Juri, Total).
     */
    public function publicSummary()
    {
        $data = Cache::remember('public_orang_summary', 3600, function() {
            $total = Orang::where('is_active', true)->count();

            // Hitung orang unik per peran
            $peranCounts = OrangStatus::join('peran', 'orang_status.peran_id', '=', 'peran.id')
                ->join('orang', 'orang_status.orang_id', '=', 'orang.id')
                ->where('orang.is_active', true)
                ->where('orang_status.is_active', true)
                ->selectRaw("peran.nama, COUNT(DISTINCT orang_status.orang_id) as jumlah")
                ->groupBy('peran.nama')
                ->pluck('jumlah', 'nama');

            return [
                'total'     => $total,
                'atlet'     => $peranCounts->get('Atlet', 0),
                'pelatih'   => $peranCounts->get('Pelatih', 0),
                'wasit'     => $peranCounts->get('Wasit/Juri', 0),
            ];
        });

        return response()->json($data);
    }

    /**
     * Public detail orang — ISO 27001: mask NIK, hide alamat/telp.
     */
    public function publicShow(int $id)
    {
        $orang = Cache::remember("public_orang_detail_{$id}", 600, function() use ($id) {
            $o = Orang::with([
                'domisili',
                'statusList.jenis', 'statusList.peran', 'statusList.cabor',
                'riwayatEvent.event', 'riwayatEvent.cabor',
            ])->where('is_active', true)->findOrFail($id);

            // ISO 27001 + UU ITE: Data minimization
            $o->nik = $this->maskNik($o->nik);
            $o->append('umur');
            $o->makeHidden(['telp', 'alamat', 'tgl_lahir']);
            return $o;
        });

        return response()->json($orang);
    }

    /**
     * ISO 27001: Mask NIK — tampilkan hanya 4 digit awal + 4 digit akhir.
     * Contoh: 3201XXXXXXXX1234
     */
    private function maskNik(?string $nik): ?string
    {
        if (!$nik || strlen($nik) < 8) return $nik;
        $len = strlen($nik);
        return substr($nik, 0, 4) . str_repeat('*', $len - 8) . substr($nik, -4);
    }
}
