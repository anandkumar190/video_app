<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Upload;
use Illuminate\Support\Facades\Gate;

class RoomViewController extends Controller
{
    public function control(Room $room)
    {
        Gate::authorize('update', $room);

        $uploads = Upload::when(auth()->user()->role !== 'host', function ($query) {
            return $query->where('user_id', auth()->id());
        })
        ->orderBy('created_at', 'desc')
        ->get();

        return view('rooms.control', [
            'room' => $room,
            'uploads' => $uploads
        ]);
    }

    public function viewer(Request $request, Room $room)
    {
        $guestName = $request->query('guest', 'Guest');
        $token = $request->query('token', '');

        return view('rooms.viewer', [
            'room' => $room,
            'guestName' => $guestName,
            'token' => $token
        ]);
    }
}