<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\KabKota;
use App\Models\LogSistem;
use Illuminate\Http\Request;

class KabKotaController extends Controller
{
    public function index(Request $request)
    {
        $q = KabKota::query();
        if ($request->search) $q->where('name', 'like', "%{$request->search}%");
        return response()->json($request->has('all') ? $q->orderBy('name')->get() : $q->orderBy('name')->paginate($request->per_page ?? 50));
    }
    public function store(Request $r) { $d = $r->validate(['name'=>'required','code'=>'required','type'=>'required|in:kabupaten,kota']); $m = KabKota::create($d); LogSistem::catat('CREATE','KabKota',"Menambah: {$m->name}"); return response()->json($m,201); }
    public function show(KabKota $kab_kotum) { return response()->json($kab_kotum); }
    public function update(Request $r, KabKota $kab_kotum) { $d = $r->validate(['name'=>'required','code'=>'required','type'=>'required|in:kabupaten,kota']); $kab_kotum->update($d); return response()->json($kab_kotum); }
    public function destroy(KabKota $kab_kotum) { $kab_kotum->delete(); return response()->json(['message'=>'Deleted']); }
}
