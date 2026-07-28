<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenancyScope;

class Prasarana extends Model
{
    use SoftDeletes, TenancyScope;

    protected $table = 'prasarana';

    protected $fillable = [
        'jenis_id', 'lokasi_id', 'nama', 'longitude', 'latitude',
        'pengelola', 'narahubung', 'telp_narahubung', 'alamat',
        'kapasitas', 'keterangan', 'kategori', 'standar'
    ];

    public function jenis()   { return $this->belongsTo(Jenis::class); }
    public function lokasi()  { return $this->belongsTo(KabKota::class, 'lokasi_id'); }
    public function fasilitas() { return $this->hasMany(FasilitasPrasarana::class); }
    public function cabors()  { return $this->belongsToMany(Cabor::class, 'cabor_prasarana'); }
    public function fotos()   { return $this->hasMany(FotoPrasarana::class); }
}
