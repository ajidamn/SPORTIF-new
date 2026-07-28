<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenancyScope;

class Orang extends Model
{
    use SoftDeletes, TenancyScope;

    protected $table = 'orang';

    protected $fillable = [
        'sportif_id', 'nik', 'nama', 'tgl_lahir', 'telp', 'alamat', 'gender',
        'disabilitas', 'jenis_disabilitas', 'foto', 'tinggi', 'berat', 'gol_darah',
        'domisili_id', 'is_active',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'disabilitas' => 'boolean',
        'is_active' => 'boolean',
        'nik' => 'encrypted',
    ];

    public function domisili()
    {
        return $this->belongsTo(KabKota::class, 'domisili_id');
    }

    public function statusList()
    {
        return $this->hasMany(OrangStatus::class);
    }

    /**
     * Riwayat event sebagai peserta utama
     */
    public function riwayatEvent()
    {
        return $this->hasMany(RiwayatEvent::class, 'orang_id');
    }

    /**
     * Event sebagai pelatih
     */
    public function riwayatSebagaiPelatih()
    {
        return $this->hasMany(RiwayatEvent::class, 'pelatih_id');
    }

    /**
     * Event sebagai wasit
     */
    public function riwayatSebagaiWasit()
    {
        return $this->hasMany(RiwayatEvent::class, 'wasit_id');
    }

    // Shortcuts
    public function getUmurAttribute()
    {
        return $this->tgl_lahir ? $this->tgl_lahir->age : null;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->sportif_id)) {
                $model->sportif_id = self::generateSportifId($model->nama, $model->gender);
            }
        });
    }

    public static function generateSportifId($nama, $gender)
    {
        $namaClean = trim(preg_replace('/[^a-zA-Z\s]/', '', $nama)); 
        $kata = explode(' ', $namaClean);
        
        if (count($kata) == 0 || empty($namaClean)) {
            $inisial = 'XX';
        } elseif (count($kata) == 1) {
            $inisial = strtoupper(substr($kata[0], 0, 2));
            if (strlen($inisial) == 1) $inisial .= 'X';
        } else {
            $inisial = strtoupper(substr($kata[0], 0, 1) . substr($kata[1], 0, 1));
        }

        $tahun = date('y');
        $g = strtoupper($gender) === 'P' ? 'P' : 'L'; 

        $lastRecord = self::whereYear('created_at', date('Y'))
                          ->whereNotNull('sportif_id')
                          ->orderBy('id', 'desc')
                          ->first();
                          
        $urutan = 1;
        if ($lastRecord && preg_match('/(\d{4})$/', $lastRecord->sportif_id, $matches)) {
            $urutan = intval($matches[1]) + 1;
        }

        $urutanStr = str_pad($urutan, 4, '0', STR_PAD_LEFT);

        return "SRF{$inisial}{$tahun}{$g}{$urutanStr}";
    }
}
