<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    protected $table = 'jenis';
    protected $fillable = ['nama'];

    public function peran()
    {
        return $this->hasMany(Peran::class);
    }

    public function organisasi()
    {
        return $this->hasMany(Organisasi::class);
    }

    public function orang_statuses()
    {
        return $this->hasMany(OrangStatus::class);
    }
}
