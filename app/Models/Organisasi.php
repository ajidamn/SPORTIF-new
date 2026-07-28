<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenancyScope;

class Organisasi extends Model
{
    use SoftDeletes, TenancyScope;

    protected $table = 'organisasi';

    protected $fillable = [
        'jenis_id', 'nama', 'alamat', 'telp', 'narahubung', 'email',
        'sk_pendirian', 'tgl_sk_pendirian', 'longitude', 'latitude',
        'status', 'skala_id', 'kab_kota_id', 'logo',
    ];

    protected $casts = ['tgl_sk_pendirian' => 'date'];

    public function jenis()   { return $this->belongsTo(Jenis::class); }
    public function skala()   { return $this->belongsTo(Skala::class); }
    public function kabKota() { return $this->belongsTo(KabKota::class); }

    public function pengurus()
    {
        return $this->hasMany(PengurusOrganisasi::class);
    }
}
