<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Room;
use App\Models\Participant;
use App\Models\Upload;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Create demo host user
        $host = User::create([
            'name' => 'Demo Host',
            'email' => 'host@livestudio.com',
            'password' => Hash::make('password'),
            'role' => 'host',
            'email_verified_at' => now(),
        ]);

        // Create demo guest user
        $guest = User::create([
            'name' => 'Demo Guest',
            'email' => 'guest@livestudio.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
            'email_verified_at' => now(),
        ]);

        // Create demo room
        $room = Room::create([
            'slug' => 'demo-studio',
            'title' => 'Demo Live Studio',
            'status' => 'offline',
        ]);

        // Add host participant to room
        Participant::create([
            'room_id' => $room->id,
            'user_id' => $host->id,
            'role' => 'host',
            'invite_token' => Str::uuid(),
            'joined_at' => now(),
        ]);

        // Add guest participant to room with pending invite
        Participant::create([
            'room_id' => $room->id,
            'user_id' => null,
            'role' => 'guest',
            'invite_token' => Str::uuid(),
            'joined_at' => null,
        ]);

        // Create demo upload for host
        Upload::create([
            'user_id' => $host->id,
            'filename' => 'demo-video.mp4',
            'path' => 'uploads/demo-video.mp4',
            'mime' => 'video/mp4',
            'size_bytes' => 15728640, // ~15MB
            'duration_sec' => 120, // 2 minutes
        ]);
    }
}
