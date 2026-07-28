<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenancyScope;

class Sekolah extends Model
{
    use SoftDeletes, TenancyScope;

    protected $table = 'sekolah';

    protected $fillable = [
        'kab_kota_id', 'nama_sekolah', 'jenis_sekolah',
        'status_sekolah', 'narahubung', 'telepon', 'created_by',
    ];

    protected $withCount = ['ekstrakurikuler'];

    public function kabKota()
    {
        return $this->belongsTo(KabKota::class);
    }

    public function ekstrakurikuler()
    {
        return $this->hasMany(EkstrakurikulerSekolah::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
