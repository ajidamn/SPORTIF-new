<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

    protected $fillable = [
        'judul', 'isi', 'file_lampiran', 'author_id',
        'status', 'target', 'tampil_mulai', 'tampil_selesai', 'is_pinned',
    ];

    protected $casts = [
        'tampil_mulai' => 'datetime',
        'tampil_selesai' => 'datetime',
        'is_pinned' => 'boolean',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope: only active announcements visible now.
     */
    public function scopeAktif($query, string $target = 'all')
    {
        return $query->where('status', 'active')
            ->where(function ($q) use ($target) {
                $q->where('target', 'all')->orWhere('target', $target);
            })
            ->where(function ($q) {
                $q->whereNull('tampil_mulai')->orWhere('tampil_mulai', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('tampil_selesai')->orWhere('tampil_selesai', '>=', now());
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');
    }
}
