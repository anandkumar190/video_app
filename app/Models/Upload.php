<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'stored_filename',
        'mime_type',
        'path',
        'size_bytes',
        'duration_sec',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'duration_sec' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
