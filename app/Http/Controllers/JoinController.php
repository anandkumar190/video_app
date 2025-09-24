<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Participant;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;

class JoinController extends Controller
{
    public function join($inviteToken)
    {
        $participant = Participant::where('invite_token', $inviteToken)
            ->whereNull('user_id')
            ->first();

        if (!$participant) {
            return view('join.invalid-token');
        }

        $room = Room::find($participant->room_id);

        if (!$room) {
            return view('join.invalid-token');
        }

        if (Auth::check()) {
            $participant->update([
                'user_id' => Auth::id(),
                'joined_at' => now()
            ]);

            return redirect()->route('room.view', $room->slug);
        }

        return view('join.guest', [
            'room' => $room,
            'token' => $inviteToken
        ]);
    }

    public function guestJoin(Request $request, $inviteToken)
    {
        $participant = Participant::where('invite_token', $inviteToken)
            ->whereNull('user_id')
            ->first();

        if (!$participant) {
            return response()->json(['message' => 'Invalid invite token'], 404);
        }

        $room = Room::find($participant->room_id);

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        $guestName = $request->input('name', 'Guest');

        session([
            'guest_mode' => true,
            'guest_name' => $guestName,
            'room_id' => $room->id,
            'participant_id' => $participant->id
        ]);

        $participant->update([
            'joined_at' => now()
        ]);

        return response()->json([
            'message' => 'Joined as guest successfully',
            'room' => $room,
            'guest_name' => $guestName
        ]);
    }
}
