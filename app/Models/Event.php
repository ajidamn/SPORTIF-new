<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\TenancyScope;

class Event extends Model
{
    use SoftDeletes, TenancyScope;

    protected $table = 'events';

    protected $fillable = [
        'kab_kota_id',
        'jenis_id',
        'nama',
        'tahun',
        'skala_id',
        'jenis_event',
        'penyelenggara',
        'lokasi_kegiatan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'disabilitas',
        'created_by',
        'approval_status',
        'dokumen_pendukung',
        'kapasitas_peserta',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'disabilitas'     => 'boolean',
    ];

    /**
     * Jenis domain: Olahraga Prestasi, Olahraga Masyarakat, dll.
     */
    public function jenis()
    {
        return $this->belongsTo(Jenis::class);
    }

    public function skala()
    {
        return $this->belongsTo(Skala::class);
    }

    /**
     * Cabor yang dipertandingkan di event ini.
     * Difilter otomatis berdasarkan jenis_id saat input.
     */
    public function cabors()
    {
        return $this->belongsToMany(Cabor::class, 'cabor_event', 'event_id', 'cabor_id')->withTimestamps();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isEditableBy(User $user): bool
    {
        if ($user->hasRole('SuperAdmin')) return true;
        return $this->created_by === $user->id;
    }

    public function needsApproval(): bool
    {
        if (!$this->created_by) return false;
        $creator = $this->creator;
        if (!$creator || !$creator->kab_kota_id) return false;
        $skala = $this->skala;
        return $skala && $skala->nama !== 'Daerah';
    }

    public function editLogs()
    {
        return $this->hasMany(EventEditLog::class);
    }

    public function riwayat()
    {
        return $this->hasMany(RiwayatEvent::class);
    }

    /**
     * Scope: hanya event yang statusnya aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Dapatkan tipe cabor yang sesuai berdasarkan jenis_id event.
     * jenis_id=1 → olahraga_prestasi, jenis_id=2 → olahraga_masyarakat
     */
    public function getCaborTipeAttribute(): ?string
    {
        return match($this->jenis_id) {
            1 => 'olahraga_prestasi',
            2 => 'olahraga_masyarakat',
            default => null,
        };
    }
}
