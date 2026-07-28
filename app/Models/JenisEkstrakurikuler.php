<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisEkstrakurikuler extends Model
{
    protected $table = 'jenis_ekstrakurikuler';

    protected $fillable = [
        'nama', 'kategori', 'cabor_id', 'keterangan', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function cabor()
    {
        return $this->belongsTo(Cabor::class);
    }

    public function ekstrakurikuler()
    {
        return $this->hasMany(EkstrakurikulerSekolah::class, 'jenis_ekskul_id');
    }
}
