<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Peran;
use Illuminate\Http\Request;

class PeranController extends Controller
{
    public function index(Request $r) {
        $q = Peran::with('jenis');
        if ($r->jenis_id) $q->where('jenis_id', $r->jenis_id);
        if ($r->search) $q->where('nama', 'like', "%{$r->search}%");
        return response()->json(
            $r->has('all') ? $q->orderBy('nama')->get() : $q->orderBy('id')->paginate($r->per_page ?? 15)
        );
    }
    public function store(Request $r) { return response()->json(Peran::create($r->validate(['jenis_id'=>'required|exists:jenis,id','nama'=>'required'])),201); }
    public function show(Peran $peran) { return response()->json($peran->load('jenis')); }
    public function update(Request $r, Peran $peran) { $peran->update($r->validate(['jenis_id'=>'required','nama'=>'required'])); return response()->json($peran); }
    public function destroy(Peran $peran) { $peran->delete(); return response()->json(['message'=>'Deleted']); }
}
