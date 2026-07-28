<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cabor;
use App\Models\Event;
use App\Models\Informasi;
use App\Models\Jenis;
use App\Models\KabKota;
use App\Models\Orang;
use App\Models\Pengumuman;
use App\Models\Prasarana;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $stats = [
            'total_orang'    => Orang::count(),
            'total_cabor'    => Cabor::count(),
            'total_kabkota'  => KabKota::count(),
            'total_prasarana'=> Prasarana::count(),
            'total_event'    => Event::count(),
        ];

        return view('public.landingpage2', compact('stats'));
    }

    public function informasiIndex()
    {
        $informasi = Informasi::where('status', 'published')->latest()->paginate(12);
        return view('public.informasi-index', compact('informasi'));
    }

    public function informasiShow($slug)
    {
        $item = Informasi::where('slug', $slug)->firstOrFail();
        return view('public.informasi-show', compact('item'));
    }

    public function orangIndex()
    {
        $jenis   = Jenis::orderBy('nama')->get(['id','nama']);
        $cabors  = Cabor::orderBy('nama')->get(['id','nama','tipe']);
        $kabKota = KabKota::orderBy('name')->get(['id','name']);
        return view('public.orang', compact('jenis', 'cabors', 'kabKota'));
    }

    public function prasaranaIndex()
    {
        $jenis   = Jenis::orderBy('nama')->get(['id','nama']);
        $cabors  = Cabor::orderBy('nama')->get(['id','nama','tipe']);
        $kabKota = KabKota::orderBy('name')->get(['id','name']);
        return view('public.prasarana', compact('jenis', 'cabors', 'kabKota'));
    }

    public function organisasiIndex()
    {
        $jenis = Jenis::orderBy('nama')->get(['id','nama']);
        return view('public.organisasi', compact('jenis'));
    }

    public function landingpage2()
    {
        $pengumuman = Pengumuman::aktif('public')->take(5)->get();
        $informasi  = Informasi::where('status', 'published')->latest()->take(6)->get();

        // Stats untuk hero counter
        $stats = [
            'total_orang'    => Orang::count(),
            'total_cabor'    => Cabor::count(),
            'total_kabkota'  => KabKota::count(),
            'total_prasarana'=> Prasarana::count(),
            'total_event'    => Event::count(),
        ];

        return view('public.landing', compact('pengumuman', 'informasi', 'stats'));
    }
}
