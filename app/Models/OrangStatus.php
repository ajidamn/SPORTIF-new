<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrangStatus extends Model
{
    protected $table = 'orang_status';

    protected $fillable = [
        'orang_id', 'jenis_id', 'peran_id', 'cabor_id',
        'organisasi_id', 'id_sitenor', 'sertifikat_profesi',
        'skala_id', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function orang()   { return $this->belongsTo(Orang::class); }
    public function jenis()   { return $this->belongsTo(Jenis::class); }
    public function peran()   { return $this->belongsTo(Peran::class); }
    public function cabor()   { return $this->belongsTo(Cabor::class); }
    public function organisasi() { return $this->belongsTo(Organisasi::class); }
    public function skala()   { return $this->belongsTo(Skala::class); }
}
