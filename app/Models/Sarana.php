<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenancyScope;

class Sarana extends Model
{
    use HasFactory, TenancyScope, SoftDeletes;

    protected $table = 'sarana';

    protected $fillable = [
        'kab_kota_id', 'jenis_id', 'kode_inventaris', 'nama_barang',
        'spesifikasi', 'kondisi', 'status', 'foto_barang', 'cabor_id',
        'posisi_aset', 'lokasi_barang', 'keterangan_lokasi', 'jumlah',
        'satuan', 'tahun_pengadaan', 'sumber_dana'
    ];

    public function kabKota() { return $this->belongsTo(KabKota::class, 'kab_kota_id'); }
    public function jenis() { return $this->belongsTo(Jenis::class); }
    public function cabor() { return $this->belongsTo(Cabor::class); }
    public function prasarana() { return $this->belongsTo(Prasarana::class, 'lokasi_barang'); }
}
