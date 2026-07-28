<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peran extends Model
{
    protected $table = 'peran';
    protected $fillable = ['jenis_id', 'nama'];

    public function jenis()
    {
        return $this->belongsTo(Jenis::class);
    }
}
