<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EkstrakurikulerSekolah extends Model
{
    use SoftDeletes;

    protected $table = 'ekstrakurikuler_sekolah';

    protected $fillable = [
        'sekolah_id', 'jenis_ekskul_id', 'nama_pembina',
        'jumlah_anggota_putra', 'jumlah_anggota_putri',
        'dokumen_jumlah_anggota', 'jadwal_pertemuan',
        'status_ekstrakurikuler', 'narahubung', 'telepon', 'created_by',
    ];

    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class);
    }

    public function jenisEkskul()
    {
        return $this->belongsTo(JenisEkstrakurikuler::class, 'jenis_ekskul_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
