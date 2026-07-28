<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengurusOrganisasi extends Model
{
    protected $table = 'pengurus_organisasi';

    protected $fillable = [
        'organisasi_id', 'ketua_id', 'sekretaris_id', 'bendahara_id',
        'jumlah_anggota', 'sk_kepengurusan', 'tgl_awal', 'tgl_akhir',
    ];

    protected $casts = ['tgl_awal' => 'date', 'tgl_akhir' => 'date'];

    public function organisasi() { return $this->belongsTo(Organisasi::class); }
    public function ketua()      { return $this->belongsTo(Orang::class, 'ketua_id'); }
    public function sekretaris() { return $this->belongsTo(Orang::class, 'sekretaris_id'); }
    public function bendahara()  { return $this->belongsTo(Orang::class, 'bendahara_id'); }
}
