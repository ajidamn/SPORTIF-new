<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FasilitasPrasarana extends Model
{
    protected $table = 'fasilitas_prasarana';

    // Struktur baru: nama, jumlah, kondisi, keterangan
    protected $fillable = [
        'prasarana_id', 'nama', 'jumlah', 'kondisi', 'keterangan',
    ];

    protected $casts = [
        'jumlah' => 'integer',
    ];

    public function prasarana()
    {
        return $this->belongsTo(Prasarana::class);
    }
}
