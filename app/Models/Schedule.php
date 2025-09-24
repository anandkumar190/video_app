<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'upload_id',
        'start_at',
        'status',
        'process_pid',
        'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'status' => 'string',
    ];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
