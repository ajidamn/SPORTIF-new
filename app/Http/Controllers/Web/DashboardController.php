<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Cabor;
use App\Models\Event;
use App\Models\Jenis;
use App\Models\KabKota;
use App\Models\LogSistem;
use App\Models\Orang;
use App\Models\OrangStatus;
use App\Models\Organisasi;
use App\Models\Pengumuman;
use App\Models\Prasarana;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if ($user) {
            $roles = $user->getRoleNames();
            $isExecRole = $roles->contains(function ($role) {
                return str_starts_with($role, 'Kepala') || str_starts_with($role, 'Ketua');
            });

            if ($isExecRole) {
                return view('admin.dashboard-eksekutif', ['user' => $user]);
            }
        }

        $kab_kota_id = $user ? $user->kab_kota_id : null;

        // ── Stat Cards (optimized: single queries where possible) ──
        $qOrang = Orang::query();
        if ($kab_kota_id) $qOrang->where('domisili_id', $kab_kota_id);
        $totalOrang = $qOrang->count();
        
        $qStatus = OrangStatus::query();
        if ($kab_kota_id) {
            $qStatus->whereHas('orang', fn($q) => $q->where('domisili_id', $kab_kota_id));
        }

        $peranCounts = (clone $qStatus)->selectRaw("
            SUM(CASE WHEN peran_id IN (SELECT id FROM peran WHERE nama = 'Atlet') THEN 1 ELSE 0 END) as atlet,
            SUM(CASE WHEN peran_id IN (SELECT id FROM peran WHERE nama = 'Pelatih') THEN 1 ELSE 0 END) as pelatih,
            SUM(CASE WHEN peran_id IN (SELECT id FROM peran WHERE nama = 'Wasit/Juri') THEN 1 ELSE 0 END) as wasit
        ")->first();

        $qEvent = Event::query();
        if ($kab_kota_id) {
            $qEvent->where(function($q) use ($kab_kota_id) {
                $q->where('kab_kota_id', $kab_kota_id)
                  ->orWhereHas('skala', fn($sq) => $sq->where('nama', '!=', 'Daerah'));
            });
        }
        
        $qOrganisasi = Organisasi::query();
        if ($kab_kota_id) {
            $qOrganisasi->where(function($q) use ($kab_kota_id) {
                $q->where('kab_kota_id', $kab_kota_id)
                  ->orWhereHas('skala', fn($sq) => $sq->where('nama', '!=', 'Daerah'));
            });
        }

        $qPrasarana = Prasarana::query();
        if ($kab_kota_id) $qPrasarana->where('lokasi_id', $kab_kota_id);

        $stats = [
            'total_orang'     => $totalOrang,
            'total_atlet'     => $peranCounts->atlet ?? 0,
            'total_pelatih'   => $peranCounts->pelatih ?? 0,
            'total_wasit'     => $peranCounts->wasit ?? 0,
            'total_cabor'     => Cabor::count(),
            'total_kabkota'   => KabKota::count(),
            'total_event'     => $qEvent->count(),
            'total_organisasi'=> $qOrganisasi->count(),
            'total_prasarana' => $qPrasarana->count(),
            'total_users'     => User::count(),
        ];

        // ── Grafik 1: Top 10 Cabor berdasarkan jumlah atlet ─
        $qTopCabor = OrangStatus::select('cabor_id', DB::raw('count(*) as total'))
            ->whereIn('peran_id', function($q) {
                $q->select('id')->from('peran')->where('nama', 'Atlet');
            });
            
        if ($kab_kota_id) {
            $qTopCabor->whereHas('orang', fn($q) => $q->where('domisili_id', $kab_kota_id));
        }

        $topCabor = $qTopCabor->whereNotNull('cabor_id')
            ->groupBy('cabor_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('cabor:id,nama')
            ->get()
            ->map(fn($s) => ['nama' => $s->cabor?->nama ?? 'Unknown', 'total' => $s->total]);

        // ── Grafik 2: Distribusi Orang per Kab/Kota (top 15) ─
        $perKabKota = Orang::select('domisili_id', DB::raw('count(*) as total'))
            ->whereNotNull('domisili_id')
            ->groupBy('domisili_id')
            ->orderByDesc('total')
            ->limit(15)
            ->with('domisili:id,name')
            ->get()
            ->map(fn($o) => ['nama' => $o->domisili?->name ?? 'Unknown', 'total' => $o->total]);

        // ── Grafik 3: Distribusi Gender ─────────────────────
        $qGender = Orang::select('gender', DB::raw('count(*) as total'))
            ->whereNotNull('gender');
        if ($kab_kota_id) $qGender->where('domisili_id', $kab_kota_id);
        
        $genderData = $qGender->groupBy('gender')
            ->pluck('total', 'gender');

        // ── Grafik 4: SDM per Jenis ──────────────────────────
        $perJenis = Jenis::withCount(['orang_statuses' => function($q) use ($kab_kota_id) {
            if ($kab_kota_id) {
                $q->whereHas('orang', fn($sq) => $sq->where('domisili_id', $kab_kota_id));
            }
        }])->get()
            ->map(fn($j) => ['nama' => $j->nama, 'total' => $j->orang_statuses_count]);

        // ── Event terbaru ─────────────────────────────────────
        $qEventTerbaru = Event::select('id', 'nama', 'status', 'created_at')->latest();
        if ($kab_kota_id) {
            $qEventTerbaru->where(function($q) use ($kab_kota_id) {
                $q->where('kab_kota_id', $kab_kota_id)
                  ->orWhereHas('skala', fn($sq) => $sq->where('nama', '!=', 'Daerah'));
            });
        }
        $eventTerbaru = $qEventTerbaru->take(5)->get();

        // ── Log Aktivitas terakhir ────────────────────────────
        $logTerbaru = LogSistem::with('user:id,name')
            ->latest()
            ->take(8)
            ->get();

        // ── Pengumuman aktif ──────────────────────────────────
        $pengumuman = Pengumuman::aktif('admin')->take(5)->get();

        return view('admin.dashboard', compact(
            'stats', 'topCabor', 'perKabKota', 'genderData', 'perJenis',
            'eventTerbaru', 'logTerbaru', 'pengumuman'
        ));
    }
}
