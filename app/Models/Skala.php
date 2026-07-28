<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skala extends Model
{
    protected $table = 'skala';
    protected $fillable = ['nama'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
