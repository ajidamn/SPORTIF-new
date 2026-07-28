<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{JenisEkstrakurikuler, Sekolah, EkstrakurikulerSekolah, LogSistem};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EkstrakurikulerController extends Controller
{
    // ══════════════════════════════════════════════════════════
    //  MASTER: Jenis Ekstrakurikuler
    // ══════════════════════════════════════════════════════════

    public function jenisIndex(Request $r)
    {
        $q = JenisEkstrakurikuler::with('cabor');
        if ($r->search) $q->where('nama', 'like', "%{$r->search}%");
        if ($r->kategori) $q->where('kategori', $r->kategori);
        if ($r->has('all')) return response()->json($q->where('is_active', true)->orderBy('nama')->get());
        return response()->json($q->latest()->paginate($r->per_page ?? 15));
    }

    public function jenisStore(Request $r)
    {
        $d = $r->validate([
            'nama'       => 'required|string|max:255',
            'kategori'   => 'required|in:olahraga,kepemimpinan,seni_budaya,akademik_sains,keagamaan',
            'cabor_id'   => 'nullable|exists:cabors,id',
            'keterangan' => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);
        // Anti double submit lock (3 detik)
        $lockKey = 'store_jenis_ekskul_' . auth()->id();
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 3)) {
            return response()->json(['message' => 'Permintaan sedang diproses. Mohon tunggu.'], 429);
        }

        // Sanitasi dasar ISO 27001
        $d['nama'] = strip_tags(trim($d['nama']));
        if (!empty($d['keterangan'])) $d['keterangan'] = strip_tags(trim($d['keterangan']));

        $m = JenisEkstrakurikuler::create($d);
        LogSistem::catat('CREATE', 'JenisEkstrakurikuler', "Menambah jenis ekskul: {$m->nama}");
        return response()->json($m, 201);
    }

    public function jenisShow(JenisEkstrakurikuler $jenisEkstrakurikuler)
    {
        return response()->json($jenisEkstrakurikuler->load('cabor'));
    }

    public function jenisUpdate(Request $r, JenisEkstrakurikuler $jenisEkstrakurikuler)
    {
        $d = $r->validate([
            'nama'       => 'required|string|max:255',
            'kategori'   => 'required|in:olahraga,kepemimpinan,seni_budaya,akademik_sains,keagamaan',
            'cabor_id'   => 'nullable|exists:cabors,id',
            'keterangan' => 'nullable|string',
            'is_active'  => 'nullable|boolean',
        ]);
        $jenisEkstrakurikuler->update($d);
        return response()->json($jenisEkstrakurikuler);
    }

    public function jenisDestroy(JenisEkstrakurikuler $jenisEkstrakurikuler)
    {
        LogSistem::catat('DELETE', 'JenisEkstrakurikuler', "Menghapus jenis ekskul: {$jenisEkstrakurikuler->nama}");
        $jenisEkstrakurikuler->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ══════════════════════════════════════════════════════════
    //  DATA: Sekolah
    // ══════════════════════════════════════════════════════════

    public function sekolahIndex(Request $r)
    {
        $q = Sekolah::with(['kabKota']);
        if ($r->search)         $q->where('nama_sekolah', 'like', "%{$r->search}%");
        if ($r->kab_kota_id)    $q->where('kab_kota_id', $r->kab_kota_id);
        if ($r->jenis_sekolah)  $q->where('jenis_sekolah', $r->jenis_sekolah);
        if ($r->status_sekolah) $q->where('status_sekolah', $r->status_sekolah);
        if ($r->has('all'))     return response()->json($q->orderBy('nama_sekolah')->get());
        return response()->json($q->latest()->paginate($r->per_page ?? 15));
    }

    public function sekolahStore(Request $r)
    {
        $d = $r->validate([
            'kab_kota_id'    => 'required|exists:kab_kota,id',
            'nama_sekolah'   => 'required|string|max:255',
            'jenis_sekolah'  => 'required|in:SMA,SMK,MA,SLB',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'narahubung'     => 'nullable|string|max:255',
            'telepon'        => 'nullable|string|max:20',
        ]);
        $d['created_by'] = auth()->id();

        // Enforce tenancy: admin kab/kota hanya bisa input di regionnya
        $user = auth()->user();
        if ($user->kab_kota_id) {
            $d['kab_kota_id'] = $user->kab_kota_id;
        }

        // Anti double submit lock
        $lockKey = 'store_sekolah_' . $user->id;
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 3)) {
            return response()->json(['message' => 'Permintaan sedang diproses. Mohon tunggu.'], 429);
        }

        // Sanitasi dasar
        $d['nama_sekolah'] = strip_tags(trim($d['nama_sekolah']));
        if (!empty($d['narahubung'])) $d['narahubung'] = strip_tags(trim($d['narahubung']));

        $m = Sekolah::create($d);
        LogSistem::catat('CREATE', 'Sekolah', "Menambah sekolah: {$m->nama_sekolah}");
        return response()->json($m->load('kabKota'), 201);
    }

    public function sekolahShow(Sekolah $sekolah)
    {
        return response()->json($sekolah->load(['kabKota', 'ekstrakurikuler.jenisEkskul']));
    }

    public function sekolahUpdate(Request $r, Sekolah $sekolah)
    {
        $d = $r->validate([
            'kab_kota_id'    => 'required|exists:kab_kota,id',
            'nama_sekolah'   => 'required|string|max:255',
            'jenis_sekolah'  => 'required|in:SMA,SMK,MA,SLB',
            'status_sekolah' => 'required|in:Negeri,Swasta',
            'narahubung'     => 'nullable|string|max:255',
            'telepon'        => 'nullable|string|max:20',
        ]);

        $user = auth()->user();
        if ($user->kab_kota_id) {
            $d['kab_kota_id'] = $user->kab_kota_id;
        }

        $sekolah->update($d);
        return response()->json($sekolah->load('kabKota'));
    }

    public function sekolahDestroy(Sekolah $sekolah)
    {
        LogSistem::catat('DELETE', 'Sekolah', "Menghapus sekolah: {$sekolah->nama_sekolah}");
        $sekolah->delete();
        return response()->json(['message' => 'Deleted']);
    }

    // ══════════════════════════════════════════════════════════
    //  DATA: Ekstrakurikuler Sekolah
    // ══════════════════════════════════════════════════════════

    public function index(Request $r)
    {
        $q = EkstrakurikulerSekolah::with(['sekolah.kabKota', 'jenisEkskul']);

        // Tenancy: filter by kab_kota_id melalui sekolah
        $user = auth()->user();
        if ($user->kab_kota_id && !$user->hasRole('SuperAdmin')) {
            $q->whereHas('sekolah', function ($sq) use ($user) {
                $sq->where('kab_kota_id', $user->kab_kota_id);
            });
        }

        if ($r->search) {
            $q->where(function ($sq) use ($r) {
                $sq->where('nama_pembina', 'like', "%{$r->search}%")
                   ->orWhereHas('sekolah', fn($s) => $s->where('nama_sekolah', 'like', "%{$r->search}%"));
            });
        }
        if ($r->sekolah_id)              $q->where('sekolah_id', $r->sekolah_id);
        if ($r->jenis_ekskul_id)         $q->where('jenis_ekskul_id', $r->jenis_ekskul_id);
        if ($r->status_ekstrakurikuler)  $q->where('status_ekstrakurikuler', $r->status_ekstrakurikuler);
        if ($r->kab_kota_id) {
            $q->whereHas('sekolah', fn($s) => $s->where('kab_kota_id', $r->kab_kota_id));
        }
        if ($r->jenis_sekolah) {
            $q->whereHas('sekolah', fn($s) => $s->where('jenis_sekolah', $r->jenis_sekolah));
        }

        return response()->json($q->latest()->paginate($r->per_page ?? 15));
    }

    public function store(Request $r)
    {
        $d = $r->validate([
            'sekolah_id'              => 'required|exists:sekolah,id',
            'jenis_ekskul_id'         => 'required|exists:jenis_ekstrakurikuler,id',
            'nama_pembina'            => 'required|string|max:255',
            'jumlah_anggota_putra'    => 'nullable|integer|min:0',
            'jumlah_anggota_putri'    => 'nullable|integer|min:0',
            'dokumen_jumlah_anggota'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'jadwal_pertemuan'        => 'nullable|string|max:255',
            'status_ekstrakurikuler'  => 'nullable|in:Aktif,Non-Aktif',
            'narahubung'              => 'nullable|string|max:255',
            'telepon'                 => 'nullable|string|max:20',
        ]);

        // Upload dokumen
        if ($r->hasFile('dokumen_jumlah_anggota')) {
            $d['dokumen_jumlah_anggota'] = $r->file('dokumen_jumlah_anggota')
                ->store('dokumen_ekskul', 'public');
        }

        $d['created_by'] = auth()->id();

        // Anti double submit lock
        $lockKey = 'store_ekskul_' . auth()->id();
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 3)) {
            return response()->json(['message' => 'Permintaan sedang diproses. Mohon tunggu.'], 429);
        }

        // Sanitasi dasar
        $d['nama_pembina'] = strip_tags(trim($d['nama_pembina']));
        if (!empty($d['jadwal_pertemuan'])) $d['jadwal_pertemuan'] = strip_tags(trim($d['jadwal_pertemuan']));
        if (!empty($d['narahubung'])) $d['narahubung'] = strip_tags(trim($d['narahubung']));

        $m = EkstrakurikulerSekolah::create($d);
        LogSistem::catat('CREATE', 'Ekstrakurikuler', "Menambah ekskul di sekolah ID:{$m->sekolah_id}");
        return response()->json($m->load(['sekolah.kabKota', 'jenisEkskul']), 201);
    }

    public function show(EkstrakurikulerSekolah $ekstrakurikuler)
    {
        return response()->json($ekstrakurikuler->load(['sekolah.kabKota', 'jenisEkskul']));
    }

    public function update(Request $r, EkstrakurikulerSekolah $ekstrakurikuler)
    {
        $d = $r->validate([
            'sekolah_id'              => 'required|exists:sekolah,id',
            'jenis_ekskul_id'         => 'required|exists:jenis_ekstrakurikuler,id',
            'nama_pembina'            => 'required|string|max:255',
            'jumlah_anggota_putra'    => 'nullable|integer|min:0',
            'jumlah_anggota_putri'    => 'nullable|integer|min:0',
            'dokumen_jumlah_anggota'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'jadwal_pertemuan'        => 'nullable|string|max:255',
            'status_ekstrakurikuler'  => 'nullable|in:Aktif,Non-Aktif',
            'narahubung'              => 'nullable|string|max:255',
            'telepon'                 => 'nullable|string|max:20',
        ]);

        if ($r->hasFile('dokumen_jumlah_anggota')) {
            // Hapus dokumen lama
            if ($ekstrakurikuler->dokumen_jumlah_anggota) {
                Storage::disk('public')->delete($ekstrakurikuler->dokumen_jumlah_anggota);
            }
            $d['dokumen_jumlah_anggota'] = $r->file('dokumen_jumlah_anggota')
                ->store('dokumen_ekskul', 'public');
        }

        $ekstrakurikuler->update($d);
        return response()->json($ekstrakurikuler->load(['sekolah.kabKota', 'jenisEkskul']));
    }

    public function destroy(EkstrakurikulerSekolah $ekstrakurikuler)
    {
        LogSistem::catat('DELETE', 'Ekstrakurikuler', "Menghapus ekskul ID:{$ekstrakurikuler->id}");
        $ekstrakurikuler->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
