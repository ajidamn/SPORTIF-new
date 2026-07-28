<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class FotoPrasarana extends Model
{
    protected $table = 'foto_prasarana';
    protected $fillable = ['prasarana_id', 'foto', 'deskripsi'];
    public function prasarana() { return $this->belongsTo(Prasarana::class); }
}
