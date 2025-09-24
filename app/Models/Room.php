<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function participants()
    {
        return $this->hasMany(Participant::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function broadcasts()
    {
        return $this->hasMany(Broadcast::class);
    }

    public function hosts()
    {
        return $this->participants()->where('role', 'host');
    }

    public function guests()
    {
        return $this->participants()->where('role', 'guest');
    }
}
