<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Pengumuman, LogSistem};
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $r) { return response()->json(Pengumuman::with('author')->latest()->paginate($r->per_page??15)); }
    public function store(Request $r)
    {
        $d=$r->validate(['judul'=>'required','isi'=>'required','status'=>'nullable|in:draft,active,expired','target'=>'nullable|in:all,public,admin','tampil_mulai'=>'nullable|date','tampil_selesai'=>'nullable|date','is_pinned'=>'nullable|boolean']);
        $d['author_id']=auth()->id();
        $m=Pengumuman::create($d);
        LogSistem::catat('CREATE','Pengumuman',"Menambah: {$m->judul}");
        return response()->json($m,201);
    }
    public function show(Pengumuman $pengumuman) { return response()->json($pengumuman); }
    public function update(Request $r, Pengumuman $pengumuman) { $d=$r->validate(['judul'=>'required','isi'=>'required','status'=>'nullable|in:draft,active,expired','target'=>'nullable|in:all,public,admin','tampil_mulai'=>'nullable|date','tampil_selesai'=>'nullable|date','is_pinned'=>'nullable|boolean']); $pengumuman->update($d); return response()->json($pengumuman); }
    public function destroy(Pengumuman $pengumuman) { LogSistem::catat('DELETE','Pengumuman',"Menghapus: {$pengumuman->judul}"); $pengumuman->delete(); return response()->json(['message'=>'Deleted']); }

    /** Public endpoint — no auth */
    public function publicIndex() { return response()->json(Pengumuman::aktif('public')->get()); }
}
