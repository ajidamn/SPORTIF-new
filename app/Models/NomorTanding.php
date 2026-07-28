<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NomorTanding extends Model
{
    protected $table = 'nomor_tanding';
    protected $fillable = ['cabor_id', 'nama', 'kategori'];

    public function cabor()
    {
        return $this->belongsTo(Cabor::class);
    }
}
