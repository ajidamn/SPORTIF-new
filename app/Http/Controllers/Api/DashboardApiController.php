<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Orang;
use App\Models\OrangStatus;
use App\Models\Prasarana;
use App\Models\Organisasi;
use App\Models\Sekolah;
use App\Models\RiwayatEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    private function getScopeParams($request)
    {
        $user = auth()->user();
        
        // Cek apakah user = Ketua NPCI
        $isNPCI = false;
        if ($user) {
            $isNPCI = $user->hasRole('Ketua NPCI Provinsi') || $user->hasRole('Ketua NPCI Kab/Kota');
        }
        
        $kab_kota_id = $user->kab_kota_id ?? $request->kab_kota_id;
        $jenis_id = $user->jenis_id ?? $request->jenis_id;
        $cabor_id = $user->cabor_id ?? $request->cabor_id;
        $disabilitas = $isNPCI ? true : (in_array($request->disabilitas, ['true', '1', 1, true], true));

        return compact('user', 'kab_kota_id', 'jenis_id', 'cabor_id', 'disabilitas', 'isNPCI');
    }

    // 1. STATS RINGKASAN
    public function stats(Request $request)
    {
        extract($this->getScopeParams($request));
        $tahun = $request->tahun;

        // Base query untuk SDM melalui OrangStatus
        $qStatus = OrangStatus::where('is_active', true);
        if ($jenis_id) $qStatus->where('jenis_id', $jenis_id);
        if ($cabor_id) $qStatus->where('cabor_id', $cabor_id);
        
        // Relasi ke orang untuk filter wilayah & disabilitas
        $qStatus->whereHas('orang', function ($q) use ($kab_kota_id, $disabilitas) {
            if ($kab_kota_id) $q->where('domisili_id', $kab_kota_id);
            if ($disabilitas) $q->where('disabilitas', true);
        });
        
        $total_orang = (clone $qStatus)->count(DB::raw('DISTINCT orang_id'));
        
        $total_atlet = (clone $qStatus)->whereHas('peran', fn($q) => $q->where('nama', 'Atlet'))->count(DB::raw('DISTINCT orang_id'));
        $total_pelatih = (clone $qStatus)->whereHas('peran', fn($q) => $q->where('nama', 'Pelatih'))->count(DB::raw('DISTINCT orang_id'));
        $total_wasit = (clone $qStatus)->whereHas('peran', fn($q) => $q->where('nama', 'Wasit/Juri'))->count(DB::raw('DISTINCT orang_id'));

        // Event
        $qEvent = Event::query();
        if ($kab_kota_id) {
            $qEvent->where(function($q) use ($kab_kota_id) {
                $q->where('kab_kota_id', $kab_kota_id)
                  ->orWhereHas('skala', function($sq) {
                      $sq->where('nama', '!=', 'Daerah');
                  });
            });
        }
        if ($jenis_id) $qEvent->where('jenis_id', $jenis_id);
        if ($cabor_id) $qEvent->whereHas('cabors', fn($q) => $q->where('cabors.id', $cabor_id));
        if ($tahun) $qEvent->where('tahun', $tahun);
        $total_event = $qEvent->count();

        // Prasarana
        $qPrasarana = Prasarana::query();
        if ($kab_kota_id) $qPrasarana->where('lokasi_id', $kab_kota_id);
        if ($jenis_id) $qPrasarana->where('jenis_id', $jenis_id);
        if ($cabor_id) $qPrasarana->whereHas('cabors', fn($q) => $q->where('cabors.id', $cabor_id));
        $total_prasarana = $qPrasarana->count();

        // Organisasi
        $qOrg = Organisasi::query();
        if ($kab_kota_id) {
            $qOrg->where(function($query) use ($kab_kota_id) {
                $query->where('kab_kota_id', $kab_kota_id)
                      ->orWhereHas('skala', function($sq) {
                          $sq->where('nama', '!=', 'Daerah');
                      });
            });
        }
        if ($jenis_id) $qOrg->where('jenis_id', $jenis_id);
        if ($cabor_id) $qOrg->where('cabor_id', $cabor_id);
        $total_organisasi = $qOrg->count();

        // Sekolah
        $total_sekolah = 0;
        if (!$jenis_id || $jenis_id == 2) { 
            $qSekolah = Sekolah::query();
            if ($kab_kota_id) $qSekolah->where('kab_kota_id', $kab_kota_id);
            $total_sekolah = $qSekolah->count();
        }

        return response()->json(compact(
            'total_orang', 'total_atlet', 'total_pelatih', 'total_wasit',
            'total_event', 'total_prasarana', 'total_organisasi', 'total_sekolah'
        ));
    }

    // 2. PRESTASI ATLET
    public function prestasi(Request $request)
    {
        extract($this->getScopeParams($request));
        
        $tahun = $request->tahun;
        $skala_id = $request->skala_id;
        $event_id = $request->event_id;
        $medali = $request->medali;
        $gender = $request->gender;

        $qRiwayat = RiwayatEvent::with(['cabor', 'event', 'orang'])->whereNotNull('medali')->where('medali', '!=', '-');

        if ($cabor_id) $qRiwayat->where('cabor_id', $cabor_id);
        if ($event_id) $qRiwayat->where('event_id', $event_id);
        if ($medali) $qRiwayat->where('medali', $medali);
        
        $qRiwayat->whereHas('event', function ($q) use ($kab_kota_id, $jenis_id, $tahun, $skala_id) {
            if ($kab_kota_id) $q->where('kab_kota_id', $kab_kota_id);
            if ($jenis_id) $q->where('jenis_id', $jenis_id);
            if ($tahun) $q->where('tahun', $tahun);
            if ($skala_id) $q->where('skala_id', $skala_id);
        });

        $qRiwayat->whereHas('orang', function ($q) use ($gender, $disabilitas, $kab_kota_id) {
            if ($gender) $q->where('gender', $gender);
            if ($disabilitas) $q->where('disabilitas', true);
            if ($kab_kota_id) $q->where('domisili_id', $kab_kota_id);
        });

        $riwayats = $qRiwayat->get();

        $rekap_medali = ['emas' => 0, 'perak' => 0, 'perunggu' => 0, 'total' => 0];
        $medali_per_cabor_map = [];
        $trend_tahunan_map = [];

        foreach ($riwayats as $r) {
            $m = strtolower($r->medali);
            if (isset($rekap_medali[$m])) {
                $rekap_medali[$m]++;
                $rekap_medali['total']++;
            }

            $caborName = $r->cabor ? $r->cabor->nama : 'Lainnya';
            if (!isset($medali_per_cabor_map[$caborName])) {
                $medali_per_cabor_map[$caborName] = ['cabor' => $caborName, 'emas' => 0, 'perak' => 0, 'perunggu' => 0, 'total' => 0];
            }
            if (isset($medali_per_cabor_map[$caborName][$m])) {
                $medali_per_cabor_map[$caborName][$m]++;
                $medali_per_cabor_map[$caborName]['total']++;
            }

            // Trend tahunan
            if (!$tahun && $r->event && $r->event->tahun) {
                $thn = $r->event->tahun;
                if (!isset($trend_tahunan_map[$thn])) {
                    $trend_tahunan_map[$thn] = ['tahun' => $thn, 'emas' => 0, 'perak' => 0, 'perunggu' => 0];
                }
                if (isset($trend_tahunan_map[$thn][$m])) {
                    $trend_tahunan_map[$thn][$m]++;
                }
            }
        }

        $medali_per_cabor = array_values($medali_per_cabor_map);
        usort($medali_per_cabor, fn($a, $b) => $b['total'] <=> $a['total']);
        $medali_per_cabor = array_slice($medali_per_cabor, 0, 10);

        $trend_tahunan = array_values($trend_tahunan_map);
        usort($trend_tahunan, fn($a, $b) => $a['tahun'] <=> $b['tahun']);

        $atlet_scores = [];
        
        foreach ($riwayats as $r) {
            if (!$r->orang_id) continue;
            
            $orang_id = $r->orang_id;
            if (!isset($atlet_scores[$orang_id])) {
                $atlet_scores[$orang_id] = [
                    'orang_id' => $orang_id,
                    'nama' => $r->orang ? $r->orang->nama : '-',
                    'cabor' => $r->cabor ? $r->cabor->nama : '-',
                    'emas' => 0,
                    'perak' => 0,
                    'perunggu' => 0,
                    'total_event' => 0,
                    'skor' => 0,
                ];
            }
            
            $atlet_scores[$orang_id]['total_event'] += 1;
            
            // Bobot Skala Event
            $skalaBobot = 1; 
            if ($r->event && $r->event->skala) {
                $namaSkala = strtolower($r->event->skala->nama);
                if (str_contains($namaSkala, 'internasional')) $skalaBobot = 4;
                elseif (str_contains($namaSkala, 'nasional')) $skalaBobot = 3;
                elseif (str_contains($namaSkala, 'provinsi')) $skalaBobot = 2;
                elseif (str_contains($namaSkala, 'kota') || str_contains($namaSkala, 'daerah')) $skalaBobot = 1;
            }

            $m = strtolower($r->medali);
            $medaliBobot = 0;
            if ($m === 'emas') {
                $atlet_scores[$orang_id]['emas']++;
                $medaliBobot = 3;
            } elseif ($m === 'perak') {
                $atlet_scores[$orang_id]['perak']++;
                $medaliBobot = 2;
            } elseif ($m === 'perunggu') {
                $atlet_scores[$orang_id]['perunggu']++;
                $medaliBobot = 1;
            }
            
            // Skor = (Bobot Medali * Bobot Skala) + 1 (Partisipasi Event)
            $skor_event = ($medaliBobot * $skalaBobot) + 1;
            $atlet_scores[$orang_id]['skor'] += $skor_event;
        }

        $top_atlet = collect(array_values($atlet_scores))
            ->sortByDesc('skor')
            ->take(10)
            ->values()
            ->all();

        return response()->json(compact('rekap_medali', 'medali_per_cabor', 'top_atlet', 'trend_tahunan'));
    }

    // 3. CHARTS
    public function charts(Request $request)
    {
        extract($this->getScopeParams($request));
        
        $qStatus = OrangStatus::where('is_active', true);
        if ($jenis_id) $qStatus->where('jenis_id', $jenis_id);
        if ($cabor_id) $qStatus->where('cabor_id', $cabor_id);
        $qStatus->whereHas('orang', function ($q) use ($kab_kota_id, $disabilitas) {
            if ($kab_kota_id) $q->where('domisili_id', $kab_kota_id);
            if ($disabilitas) $q->where('disabilitas', true);
        });

        // Cabor
        $perCabor = (clone $qStatus)->with('cabor')->get()->groupBy('cabor_id')->map(function ($items) {
            return [
                'nama' => $items->first()->cabor ? $items->first()->cabor->nama : 'Belum Ditentukan',
                'total' => $items->unique('orang_id')->count()
            ];
        })->sortByDesc('total')->take(10)->values();

        // Jenis
        $perJenis = (clone $qStatus)->with('jenis')->get()->groupBy('jenis_id')->map(function ($items) {
            return [
                'nama' => $items->first()->jenis ? $items->first()->jenis->nama : 'Belum Ditentukan',
                'total' => $items->unique('orang_id')->count()
            ];
        })->values();

        // Gender & Kab/Kota
        $qOrang = Orang::whereHas('statusList', function($q) use ($jenis_id, $cabor_id) {
            if ($jenis_id) $q->where('jenis_id', $jenis_id);
            if ($cabor_id) $q->where('cabor_id', $cabor_id);
        });
        if ($kab_kota_id) $qOrang->where('domisili_id', $kab_kota_id);
        if ($disabilitas) $qOrang->where('disabilitas', true);
        
        $gender_L = (clone $qOrang)->where('gender', 'L')->count();
        $gender_P = (clone $qOrang)->where('gender', 'P')->count();
        $gender = ['L' => $gender_L, 'P' => $gender_P];

        $per_kab_kota = (clone $qOrang)->with('domisili')->get()->groupBy('domisili_id')->map(function ($items) {
            return [
                'nama' => $items->first()->domisili ? $items->first()->domisili->name : 'Lainnya',
                'total' => $items->count()
            ];
        })->sortByDesc('total')->take(10)->values();

        return response()->json([
            'top_cabor' => $perCabor,
            'gender' => $gender,
            'per_kab_kota' => $per_kab_kota,
            'per_jenis' => $perJenis
        ]);
    }

    // 4. SDM RINGKASAN
    public function sdmRingkasan(Request $request)
    {
        extract($this->getScopeParams($request));
        
        $qOrang = Orang::whereHas('statusList', function($q) use ($jenis_id, $cabor_id, $request) {
            if ($jenis_id) $q->where('jenis_id', $jenis_id);
            if ($cabor_id) $q->where('cabor_id', $cabor_id);
            if ($request->peran) $q->whereHas('peran', fn($p) => $p->where('nama', $request->peran));
        });
        
        if ($kab_kota_id) $qOrang->where('domisili_id', $kab_kota_id);
        if ($disabilitas) $qOrang->where('disabilitas', true);
        if ($request->gender) $qOrang->where('gender', $request->gender);

        $orangs = $qOrang->get();
        if ($request->rentang_usia) {
             $orangs = $orangs->filter(function($o) use ($request) {
                 $umur = $o->umur;
                 if ($request->rentang_usia == '<17') return $umur < 17;
                 if ($request->rentang_usia == '17-25') return $umur >= 17 && $umur <= 25;
                 if ($request->rentang_usia == '26-35') return $umur >= 26 && $umur <= 35;
                 if ($request->rentang_usia == '>35') return $umur > 35;
                 return true;
             });
        }

        // Distribusi Usia
        $usia = ['<17' => 0, '17-25' => 0, '26-35' => 0, '>35' => 0];
        foreach($orangs as $o) {
            $u = $o->umur;
            if ($u < 17) $usia['<17']++;
            elseif ($u <= 25) $usia['17-25']++;
            elseif ($u <= 35) $usia['26-35']++;
            else $usia['>35']++;
        }
        $per_usia = [];
        foreach($usia as $range => $total) {
            $per_usia[] = ['range' => $range, 'total' => $total];
        }

        return response()->json([
             'total' => $orangs->count(),
             'per_gender' => [
                 'L' => $orangs->where('gender', 'L')->count(),
                 'P' => $orangs->where('gender', 'P')->count(),
             ],
             'per_usia' => $per_usia,
        ]);
    }

    // 5. EVENT TERKINI
    public function eventTerkini(Request $request)
    {
        extract($this->getScopeParams($request));
        
        $qEvent = Event::with(['jenis', 'skala'])->orderBy('tanggal_mulai', 'desc');
        if ($kab_kota_id) $qEvent->where('kab_kota_id', $kab_kota_id);
        if ($jenis_id) $qEvent->where('jenis_id', $jenis_id);
        if ($cabor_id) $qEvent->whereHas('cabors', fn($q) => $q->where('cabors.id', $cabor_id));
        
        if ($request->tahun) $qEvent->where('tahun', $request->tahun);
        if ($request->status) $qEvent->where('status', $request->status);
        if ($request->skala_id) $qEvent->where('skala_id', $request->skala_id);
        if ($request->jenis_event) $qEvent->where('jenis_event', $request->jenis_event);
        
        $limit = $kab_kota_id ? ($request->limit ?? 100) : ($request->limit ?? 10);
        $events = $qEvent->take($limit)->get();
        
        return response()->json([
            'events' => $events,
            'is_kab_kota' => !empty($kab_kota_id)
        ]);
    }
    // 6. ATLET RIWAYAT
    public function atletRiwayat(Request $request, $orang_id)
    {
        extract($this->getScopeParams($request));

        $orang = Orang::find($orang_id);
        if (!$orang) return response()->json(['message' => 'Atlet tidak ditemukan'], 404);

        // Pastikan hanya ambil history yang authorized (auto-scoped)
        $qRiwayat = RiwayatEvent::with(['cabor', 'event.skala'])
            ->where('orang_id', $orang_id)
            ->whereNotNull('medali')
            ->where('medali', '!=', '-');

        // Apply scopes to events (same as prestasi method)
        $tahun = $request->tahun;
        $skala_id = $request->skala_id;
        
        $qRiwayat->whereHas('event', function ($q) use ($kab_kota_id, $jenis_id, $tahun, $skala_id) {
            if ($kab_kota_id) $q->where('kab_kota_id', $kab_kota_id);
            if ($jenis_id) $q->where('jenis_id', $jenis_id);
            if ($tahun) $q->where('tahun', $tahun);
            if ($skala_id) $q->where('skala_id', $skala_id);
        });

        if ($cabor_id) {
            $qRiwayat->where('cabor_id', $cabor_id);
        }

        $riwayats = $qRiwayat->orderBy('tanggal', 'desc')->get()->map(function($r) {
            return [
                'event_nama' => $r->event ? $r->event->nama : 'Unknown',
                'event_tahun' => $r->event ? $r->event->tahun : null,
                'skala' => ($r->event && $r->event->skala) ? $r->event->skala->nama : '-',
                'cabor' => $r->cabor ? $r->cabor->nama : '-',
                'kategori' => $r->kategori,
                'prestasi' => $r->prestasi,
                'medali' => $r->medali,
                'tanggal' => $r->tanggal ? $r->tanggal->format('Y-m-d') : null
            ];
        });

        return response()->json([
            'atlet' => [
                'nama' => $orang->nama,
                'gender' => $orang->gender == 'L' ? 'Laki-laki' : 'Perempuan',
                'umur' => $orang->umur
            ],
            'riwayat' => $riwayats
        ]);
    }

    // 7. EVENT STATS
    public function eventStats(Request $request)
    {
        extract($this->getScopeParams($request));
        $tahun = $request->tahun ?? date('Y');

        $qEvent = Event::where('approval_status', 'approved')
                        ->where('status', '!=', 'dibatalkan');
        if ($jenis_id) $qEvent->where('jenis_id', $jenis_id);
        if ($kab_kota_id) {
            $qEvent->where(function($q) use ($kab_kota_id) {
                $q->where('kab_kota_id', $kab_kota_id)
                  ->orWhereHas('skala', fn($sq) => $sq->where('nama', '!=', 'Daerah'));
            });
        }
        if ($tahun) $qEvent->where('tahun', $tahun);

        $eventIds = (clone $qEvent)->pluck('id');
        
        $perCabor = DB::table('cabor_event')
            ->join('cabors', 'cabors.id', '=', 'cabor_event.cabor_id')
            ->whereIn('cabor_event.event_id', $eventIds)
            ->select('cabors.nama', DB::raw('COUNT(DISTINCT cabor_event.event_id) as total'))
            ->groupBy('cabors.id', 'cabors.nama')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $perKabKota = (clone $qEvent)
            ->join('kab_kota', 'kab_kota.id', '=', 'events.kab_kota_id')
            ->select('kab_kota.name as nama', DB::raw('COUNT(*) as total'))
            ->groupBy('kab_kota.id', 'kab_kota.name')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $perSkala = (clone $qEvent)
            ->join('skala', 'skala.id', '=', 'events.skala_id')
            ->select('skala.nama', DB::raw('COUNT(*) as total'))
            ->groupBy('skala.id', 'skala.nama')
            ->get();

        $perStatus = (clone $qEvent)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $perBulan = (clone $qEvent)
            ->selectRaw("MONTH(tanggal_mulai) as bulan, COUNT(*) as total")
            ->whereNotNull('tanggal_mulai')
            ->groupByRaw('MONTH(tanggal_mulai)')
            ->orderBy('bulan')
            ->get();

        return response()->json(compact(
            'perCabor', 'perKabKota', 'perSkala', 'perStatus', 'perBulan'
        ));
    }
}
