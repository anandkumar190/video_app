<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'started_at',
        'ended_at',
        'type',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'type' => 'string',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}
