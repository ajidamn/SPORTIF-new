<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Informasi extends Model
{
    protected $table = 'informasi';

    protected $fillable = [
        'judul', 'isi', 'file_pendukung', 'slug', 'gambar',
        'author_id', 'status',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
