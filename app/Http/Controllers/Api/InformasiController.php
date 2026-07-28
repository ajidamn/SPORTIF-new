<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\{Informasi, LogSistem};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InformasiController extends Controller
{
    public function index(Request $r) { return response()->json(Informasi::with('author')->latest()->paginate($r->per_page??15)); }
    public function store(Request $r)
    {
        $d=$r->validate(['judul'=>'required','isi'=>'required','status'=>'nullable|in:draft,published']);
        $d['slug']=Str::slug($d['judul']).'-'.Str::random(5);
        $d['author_id']=auth()->id();
        $m=Informasi::create($d);
        LogSistem::catat('CREATE','Informasi',"Menambah: {$m->judul}");
        return response()->json($m,201);
    }
    public function show(Informasi $informasi) { return response()->json($informasi->load('author')); }
    public function update(Request $r, Informasi $informasi) { $d=$r->validate(['judul'=>'required','isi'=>'required','status'=>'nullable|in:draft,published']); $informasi->update($d); return response()->json($informasi); }
    public function destroy(Informasi $informasi) { LogSistem::catat('DELETE','Informasi',"Menghapus: {$informasi->judul}"); $informasi->delete(); return response()->json(['message'=>'Deleted']); }
}
