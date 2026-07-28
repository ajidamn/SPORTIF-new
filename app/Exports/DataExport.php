<?php

namespace App\Exports;

use App\Models\{Orang, Prasarana, Sarana, Event, Organisasi, Informasi, Pengumuman, LogSistem, User, Sekolah, EkstrakurikulerSekolah};
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection, WithHeadings, WithStyles, ShouldAutoSize};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    private string $type;
    private array $filters;

    public function __construct(string $type, array $filters = [])
    {
        $this->type    = $type;
        $this->filters = $filters;
    }

    public function headings(): array
    {
        return match ($this->type) {
            'orang'      => ['ID','NIK','Nama','Tgl Lahir','Gender','Telp','Alamat','Domisili','Disabilitas','Jenis Disabilitas','Tinggi','Berat','Gol Darah','Aktif','Jenis','Peran','Cabor'],
            'prasarana'  => ['ID','Nama','Jenis','Lokasi','Pengelola','Narahubung','Telp','Alamat','Kapasitas','Keterangan'],
            'sarana'     => ['ID','Nama Barang','Kode Inventaris','Jenis','Cabor','Kondisi','Status','Posisi Aset','Lokasi','Keterangan Lokasi','Jumlah','Satuan','Tahun','Sumber Dana','Kab/Kota'],
            'events'     => ['ID','Nama','Jenis','Skala','Jenis Event','Penyelenggara','Lokasi','Tgl Mulai','Tgl Selesai','Status'],
            'organisasi' => ['ID','Nama','Jenis','Alamat','Telp','Email','Narahubung','SK Pendirian','Kab/Kota','Status','Skala'],
            'informasi'  => ['ID','Judul','Isi','Status','Tanggal'],
            'pengumuman' => ['ID','Judul','Isi','Status','Target','Tampil Mulai','Tampil Selesai','Pinned'],
            'log-sistem' => ['ID','Waktu','User','IP Address','Aksi','Modul','Deskripsi'],
            'users'      => ['ID','Nama','Email','Role','Aktif'],
            'operators'  => ['ID','NIK','Nama','NIP','Jabatan','Role','Skala','Kab/Kota','Email','No Telp'],
            'sekolah'    => ['ID','Nama','Jenis','Status','Kab/Kota','Narahubung','Telepon','Jml Ekskul'],
            'ekstrakurikuler' => ['ID','Sekolah','Jenis Ekskul','Pembina','Putra','Putri','Jadwal','Status'],
            default      => [],
        };
    }

    public function collection(): Collection
    {
        return match ($this->type) {
            'orang'      => $this->orang(),
            'prasarana'  => $this->prasarana(),
            'sarana'     => $this->sarana(),
            'events'     => $this->events(),
            'organisasi' => $this->organisasi(),
            'informasi'  => $this->informasi(),
            'pengumuman' => $this->pengumuman(),
            'log-sistem' => $this->logSistem(),
            'users'      => $this->users(),
            'operators'  => $this->operators(),
            'sekolah'    => $this->sekolah(),
            'ekstrakurikuler' => $this->ekstrakurikuler(),
            default      => collect(),
        };
    }

    public function styles(Worksheet $s): array
    {
        return [1 => [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A56DB']],
        ]];
    }

    // ── Exporters ──────────────────────────────────────────

    private function orang(): Collection
    {
        $q = Orang::with(['domisili','statusList.jenis','statusList.peran','statusList.cabor']);
        if (!empty($this->filters['domisili_id'])) $q->where('domisili_id', $this->filters['domisili_id']);
        if (!empty($this->filters['gender']))      $q->where('gender', $this->filters['gender']);
        if (!empty($this->filters['jenis_id']))    $q->whereHas('statusList', fn($sq) => $sq->where('jenis_id', $this->filters['jenis_id']));
        if (!empty($this->filters['peran_id']))    $q->whereHas('statusList', fn($sq) => $sq->where('peran_id', $this->filters['peran_id']));
        if (!empty($this->filters['cabor_id']))    $q->whereHas('statusList', fn($sq) => $sq->where('cabor_id', $this->filters['cabor_id']));

        return $q->limit(10000)->get()->map(fn($o) => [
            $o->id, $o->nik, $o->nama, $o->tgl_lahir?->format('Y-m-d'), $o->gender,
            $o->telp, $o->alamat, $o->domisili?->name, $o->disabilitas ? 'Ya' : 'Tidak',
            $o->jenis_disabilitas ?? '-',
            $o->tinggi, $o->berat, $o->gol_darah, $o->is_active ? 'Aktif' : 'Non-Aktif',
            $o->statusList->pluck('jenis.nama')->filter()->unique()->implode(', '),
            $o->statusList->pluck('peran.nama')->filter()->unique()->implode(', '),
            $o->statusList->pluck('cabor.nama')->filter()->unique()->implode(', '),
        ]);
    }

    private function prasarana(): Collection
    {
        $q = Prasarana::with(['jenis','lokasi']);
        if (!empty($this->filters['lokasi_id'])) $q->where('lokasi_id', $this->filters['lokasi_id']);
        if (!empty($this->filters['pengelola'])) $q->where('pengelola', $this->filters['pengelola']);

        return $q->limit(10000)->get()->map(fn($p) => [
            $p->id, $p->nama, $p->jenis?->nama, $p->lokasi?->name,
            $p->pengelola, $p->narahubung, $p->telp_narahubung,
            $p->alamat, $p->kapasitas, $p->keterangan,
        ]);
    }

    private function sarana(): Collection
    {
        $q = Sarana::with(['jenis','cabor','prasarana','kabKota']);
        if (!empty($this->filters['jenis_id'])) $q->where('jenis_id', $this->filters['jenis_id']);
        if (!empty($this->filters['kondisi'])) $q->where('kondisi', $this->filters['kondisi']);
        if (!empty($this->filters['status'])) $q->where('status', $this->filters['status']);
        if (!empty($this->filters['posisi_aset'])) $q->where('posisi_aset', $this->filters['posisi_aset']);

        return $q->limit(10000)->get()->map(fn($s) => [
            $s->id, $s->nama_barang, $s->kode_inventaris, $s->jenis?->nama, $s->cabor?->nama,
            $s->kondisi, $s->status, $s->posisi_aset, $s->prasarana?->nama, $s->keterangan_lokasi,
            $s->jumlah, $s->satuan, $s->tahun_pengadaan, $s->sumber_dana, $s->kabKota?->name,
        ]);
    }

    private function events(): Collection
    {
        $q = Event::with(['jenis','skala']);
        if (!empty($this->filters['jenis_id']))    $q->where('jenis_id', $this->filters['jenis_id']);
        if (!empty($this->filters['skala_id']))    $q->where('skala_id', $this->filters['skala_id']);
        if (!empty($this->filters['status']))      $q->where('status', $this->filters['status']);
        if (!empty($this->filters['jenis_event'])) $q->where('jenis_event', $this->filters['jenis_event']);

        return $q->limit(10000)->get()->map(fn($e) => [
            $e->id, $e->nama, $e->jenis?->nama, $e->skala?->nama,
            $e->jenis_event, $e->penyelenggara, $e->lokasi_kegiatan,
            $e->tanggal_mulai?->format('Y-m-d'), $e->tanggal_selesai?->format('Y-m-d'), $e->status,
        ]);
    }

    private function organisasi(): Collection
    {
        $q = Organisasi::with(['jenis','skala','kabKota']);
        if (!empty($this->filters['jenis_id']))    $q->where('jenis_id', $this->filters['jenis_id']);
        if (!empty($this->filters['kab_kota_id'])) $q->where('kab_kota_id', $this->filters['kab_kota_id']);
        if (!empty($this->filters['status']))      $q->where('status', $this->filters['status']);

        return $q->limit(10000)->get()->map(fn($o) => [
            $o->id, $o->nama, $o->jenis?->nama, $o->alamat, $o->telp,
            $o->email, $o->narahubung, $o->sk_pendirian,
            $o->kabKota?->name, $o->status, $o->skala?->nama,
        ]);
    }

    private function informasi(): Collection
    {
        return Informasi::latest()->get()->map(fn($i) => [
            $i->id, $i->judul, strip_tags($i->isi ?? ''), $i->status, $i->created_at?->format('Y-m-d'),
        ]);
    }

    private function pengumuman(): Collection
    {
        return Pengumuman::latest()->get()->map(fn($p) => [
            $p->id, $p->judul, strip_tags($p->isi ?? ''), $p->status, $p->target,
            $p->tampil_mulai, $p->tampil_selesai, $p->is_pinned ? 'Ya' : 'Tidak',
        ]);
    }

    private function logSistem(): Collection
    {
        return LogSistem::with('user')->latest()->limit(5000)->get()->map(fn($l) => [
            $l->id, $l->created_at?->format('Y-m-d H:i:s'), $l->user_name ?? $l->user?->name,
            $l->ip_address ?? '-', $l->action, $l->module, $l->description,
        ]);
    }

    private function users(): Collection
    {
        return User::with('roles')->get()->map(fn($u) => [
            $u->id, $u->name, $u->email,
            $u->roles->pluck('name')->implode(', '),
            $u->is_active ? 'Aktif' : 'Non-Aktif',
        ]);
    }

    private function operators(): Collection
    {
        $q = \App\Models\Operator::with(['role', 'skala', 'kabKota']);
        if (!empty($this->filters['skala_id'])) $q->where('skala_id', $this->filters['skala_id']);
        if (!empty($this->filters['kabkota_id'])) $q->where('kabkota_id', $this->filters['kabkota_id']);
        if (!empty($this->filters['cabor_id'])) $q->where('cabor_id', $this->filters['cabor_id']);
        
        return $q->limit(10000)->get()->map(fn($o) => [
            $o->id, $o->nik, $o->nama, $o->nip, $o->jabatan,
            $o->role?->name, $o->skala?->nama, $o->kabKota?->name,
            $o->email, $o->no_telp,
        ]);
    }

    private function sekolah(): Collection
    {
        $q = Sekolah::with('kabKota');
        if (!empty($this->filters['kab_kota_id'])) $q->where('kab_kota_id', $this->filters['kab_kota_id']);
        if (!empty($this->filters['jenis_id'])) $q->where('jenis_id', $this->filters['jenis_id']);
        
        return $q->limit(10000)->get()->map(fn($s) => [
            $s->id, $s->nama_sekolah, $s->jenis?->nama, $s->status, $s->kabKota?->name,
            $s->nama_narahubung, $s->no_telp, $s->ekstrakurikuler()->count(),
        ]);
    }

    private function ekstrakurikuler(): Collection
    {
        $q = EkstrakurikulerSekolah::with(['sekolah', 'jenisEkskul']);
        if (!empty($this->filters['sekolah_id'])) $q->where('sekolah_id', $this->filters['sekolah_id']);
        
        return $q->limit(10000)->get()->map(fn($e) => [
            $e->id, $e->sekolah?->nama_sekolah, $e->jenisEkskul?->nama, $e->nama_pembina,
            $e->jumlah_siswa_putra, $e->jumlah_siswa_putri, $e->jadwal_latihan, $e->status,
        ]);
    }
}
