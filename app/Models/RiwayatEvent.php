<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiwayatEvent extends Model
{
    protected $table = 'riwayat_event';

    protected $fillable = [
        'event_id', 'cabor_id', 'orang_id',
        'pelatih_id', 'wasit_id', 'kab_kota_id',
        'kategori', 'prestasi', 'medali',
        'tanggal', 'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function cabor()
    {
        return $this->belongsTo(Cabor::class);
    }

    public function kab_kota()
    {
        return $this->belongsTo(KabKota::class, 'kab_kota_id');
    }

    /**
     * Peserta utama (atlet/orang yang berprestasi)
     */
    public function orang()
    {
        return $this->belongsTo(Orang::class, 'orang_id');
    }

    /**
     * Pelatih pendamping (opsional, harus orang yang punya peran Pelatih)
     */
    public function pelatih()
    {
        return $this->belongsTo(Orang::class, 'pelatih_id');
    }

    /**
     * Wasit/Juri (opsional)
     */
    public function wasit()
    {
        return $this->belongsTo(Orang::class, 'wasit_id');
    }

    /**
     * Scope: filter berdasarkan medali
     */
    public function scopeMedali($query, string $medali)
    {
        return $query->where('medali', $medali);
    }

    /**
     * Scope: filter berdasarkan orang tertentu (riwayat pribadi)
     */
    public function scopeUntukOrang($query, int $orangId)
    {
        return $query->where('orang_id', $orangId);
    }
}
