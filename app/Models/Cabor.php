<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cabor extends Model
{
    use SoftDeletes;

    protected $table = 'cabors';

    protected $fillable = ['nama', 'tipe', 'nama_pengprov', 'keterangan', 'logo'];

    /**
     * Scope untuk cabor olahraga prestasi (KONI)
     */
    public function scopePrestasi($query)
    {
        return $query->where('tipe', 'olahraga_prestasi');
    }

    /**
     * Scope untuk cabor olahraga masyarakat (KORMI)
     */
    public function scopeMasyarakat($query)
    {
        return $query->where('tipe', 'olahraga_masyarakat');
    }

    public function nomorTanding()
    {
        return $this->hasMany(NomorTanding::class);
    }

    public function orangStatus()
    {
        return $this->hasMany(OrangStatus::class);
    }

    public function prasarana()
    {
        return $this->belongsToMany(Prasarana::class, 'cabor_prasarana', 'cabor_id', 'prasarana_id');
    }
}
