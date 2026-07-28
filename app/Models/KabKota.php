<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KabKota extends Model
{
    protected $table = 'kab_kota';
    protected $fillable = ['name', 'code', 'type', 'logo', 'latitude', 'longitude'];
}
