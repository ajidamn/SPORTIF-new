<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Organisasi, LogSistem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class OrganisasiController extends Controller
{
    public function index(Request $r)
    {
        $q = Organisasi::with(['jenis', 'kabKota']);

        $user = auth()->user();
        if ($user && $user->kab_kota_id) {
            $q->where(function($query) use ($user) {
                $query->where('kab_kota_id', $user->kab_kota_id)
                      ->orWhereHas('skala', function($sq) {
                          $sq->where('nama', '!=', 'Daerah');
                      });
            });
        }

        if ($r->search)      $q->where('nama', 'like', "%{$r->search}%");
        if ($r->jenis_id)    $q->where('jenis_id', $r->jenis_id);
        if ($r->status)      $q->where('status', $r->status);
        
        if ($r->kab_kota_id) {
            if (!($user && $user->kab_kota_id && $user->kab_kota_id == $r->kab_kota_id)) {
                $q->where('kab_kota_id', $r->kab_kota_id);
            }
        }
        return response()->json($q->latest()->paginate($r->per_page ?? 15));
    }

    public function publicIndex(Request $r)
    {
        $q = Organisasi::with(['jenis', 'kabKota', 'skala']);
        // Hanya yang aktif untuk publik
        $q->where('status', 'aktif');
        
        if ($r->jenis_id)    $q->where('jenis_id', $r->jenis_id);
        if ($r->kab_kota_id) $q->where('kab_kota_id', $r->kab_kota_id);
        
        if ($r->search) {
            $q->where('nama', 'like', "%{$r->search}%");
        }
        
        if ($r->skala) {
            $q->whereHas('skala', function($query) use ($r) {
                // If front-end sends Kabupaten/Kota, map it to Daerah if needed, or just match exactly
                if ($r->skala == 'Kabupaten/Kota') {
                    $query->whereIn('nama', ['Kabupaten/Kota', 'Daerah']);
                } else {
                    $query->where('nama', $r->skala);
                }
            });
        }
        
        return response()->json($q->latest()->paginate($r->per_page ?? 12));
    }

    public function store(Request $r) 
    { 
        $d = $r->validate([
            'jenis_id' => 'required|exists:jenis,id',
            'nama' => 'required',
            'alamat' => 'nullable',
            'telp' => 'nullable',
            'narahubung' => 'nullable',
            'email' => 'nullable|email',
            'sk_pendirian' => 'nullable',
            'tgl_sk_pendirian' => 'nullable|date',
            'longitude' => 'nullable',
            'latitude' => 'nullable',
            'status' => 'nullable',
            'skala_id' => 'nullable|exists:skala,id',
            'kab_kota_id' => 'nullable|exists:kab_kota,id',
            'logo' => 'nullable|image|max:2048'
        ]);

        if ($r->hasFile('logo')) {
            $d['logo'] = $this->uploadWebp($r->file('logo'));
        }

        $m = Organisasi::create($d); 
        LogSistem::catat('CREATE','Organisasi',"Menambah: {$m->nama}"); 
        return response()->json($m, 201); 
    }

    public function show(Organisasi $organisasi) 
    { 
        return response()->json($organisasi->load(['jenis','skala','kabKota','pengurus.ketua','pengurus.sekretaris'])); 
    }

    public function publicShow($id) 
    { 
        $organisasi = Organisasi::with(['jenis','skala','kabKota','pengurus.ketua','pengurus.sekretaris'])->findOrFail($id);
        return response()->json($organisasi); 
    }

    public function update(Request $r, Organisasi $organisasi) 
    { 
        $user = auth()->user();
        if ($user && $user->kab_kota_id && $organisasi->kab_kota_id !== $user->kab_kota_id) {
            return response()->json(['message' => 'Unauthorized. Hanya bisa mengedit organisasi dari Kab/Kota Anda sendiri.'], 403);
        }

        $d = $r->validate([
            'jenis_id' => 'required|exists:jenis,id',
            'nama' => 'required',
            'alamat' => 'nullable',
            'telp' => 'nullable',
            'narahubung' => 'nullable',
            'email' => 'nullable|email',
            'sk_pendirian' => 'nullable',
            'tgl_sk_pendirian' => 'nullable|date',
            'longitude' => 'nullable',
            'latitude' => 'nullable',
            'status' => 'nullable',
            'skala_id' => 'nullable|exists:skala,id',
            'kab_kota_id' => 'nullable|exists:kab_kota,id',
            'logo' => 'nullable|image|max:2048'
        ]);

        if ($r->hasFile('logo')) {
            if ($organisasi->logo) {
                Storage::disk('public')->delete($organisasi->logo);
            }
            $d['logo'] = $this->uploadWebp($r->file('logo'));
        }

        $organisasi->update($d); 
        LogSistem::catat('UPDATE','Organisasi',"Update: {$organisasi->nama}"); 
        return response()->json($organisasi); 
    }

    public function destroy(Organisasi $organisasi) 
    { 
        $user = auth()->user();
        if ($user && $user->kab_kota_id && $organisasi->kab_kota_id !== $user->kab_kota_id) {
            return response()->json(['message' => 'Unauthorized. Hanya bisa menghapus organisasi dari Kab/Kota Anda sendiri.'], 403);
        }

        if ($organisasi->logo) {
            Storage::disk('public')->delete($organisasi->logo);
        }
        LogSistem::catat('DELETE','Organisasi',"Menghapus: {$organisasi->nama}"); 
        $organisasi->delete(); 
        return response()->json(['message'=>'Deleted']); 
    }

    private function uploadWebp($file)
    {
        $manager = new ImageManager(new Driver());
        $img = $manager->read($file->getRealPath());
        
        $filename = 'organisasi/' . uniqid() . '.webp';
        
        Storage::disk('public')->makeDirectory('organisasi');
        
        $encoded = $img->toWebp(80);
        Storage::disk('public')->put($filename, $encoded->toString());
        
        return $filename;
    }
}
