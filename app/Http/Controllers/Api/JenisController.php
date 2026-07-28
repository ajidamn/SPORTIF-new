<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Jenis, Peran, LogSistem};
use Illuminate\Http\Request;

class JenisController extends Controller
{
    public function index(Request $r) {
        $q = Jenis::with('peran');
        if ($r->search) $q->where('nama', 'like', "%{$r->search}%");
        return response()->json(
            $r->has('all') ? $q->orderBy('nama')->get() : $q->orderBy('id')->paginate($r->per_page ?? 15)
        );
    }
    public function store(Request $r) { $m = Jenis::create($r->validate(['nama'=>'required'])); LogSistem::catat('CREATE','Jenis',"Menambah: {$m->nama}"); return response()->json($m,201); }
    public function show(Jenis $jeni) { return response()->json($jeni->load('peran')); }
    public function update(Request $r, Jenis $jeni) { $jeni->update($r->validate(['nama'=>'required'])); return response()->json($jeni); }
    public function destroy(Jenis $jeni) { $jeni->delete(); return response()->json(['message'=>'Deleted']); }
}
