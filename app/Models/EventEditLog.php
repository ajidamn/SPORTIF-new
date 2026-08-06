<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventEditLog extends Model
{
    protected $fillable = ['event_id', 'user_id', 'action', 'changes', 'ip_address'];
    
    protected $casts = [
        'changes' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
