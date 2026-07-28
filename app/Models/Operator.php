<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Operator extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nik', 'nama', 'role_id', 'skala_id', 'cabor_id',
        'nip', 'jabatan', 'email', 'no_telp', 'kabkota_id', 'user_id',
    ];

    protected $casts = [
        'nik' => 'encrypted',
    ];

    public function skala()
    {
        return $this->belongsTo(Skala::class);
    }

    public function cabor()
    {
        return $this->belongsTo(Cabor::class);
    }

    public function kabKota()
    {
        return $this->belongsTo(KabKota::class, 'kabkota_id');
    }

    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
