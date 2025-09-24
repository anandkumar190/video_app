<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Participant;
use App\Models\Broadcast;
use App\Models\Schedule;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Services\TokenServiceInterface;

class RoomController extends Controller
{
    private TokenServiceInterface $tokenService;

    public function __construct(TokenServiceInterface $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Room::class);

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:rooms|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $slug = $request->slug ?: Str::slug($request->title . '-' . Str::random(6));

        $room = Room::create([
            'title' => $request->title,
            'slug' => $slug,
            'status' => 'offline',
        ]);

        // Add the creator as a host participant
        Participant::create([
            'room_id' => $room->id,
            'user_id' => auth()->id(),
            'role' => 'host',
            'invite_token' => Str::uuid(),
            'joined_at' => now(),
        ]);

        return response()->json([
            'message' => 'Room created successfully',
            'room' => $room
        ], 201);
    }

    public function createInvites(Room $room, Request $request)
    {
        Gate::authorize('update', $room);

        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $invites = [];
        for ($i = 0; $i < $request->count; $i++) {
            $participant = Participant::create([
                'room_id' => $room->id,
                'user_id' => null,
                'role' => 'guest',
                'invite_token' => Str::uuid(),
                'joined_at' => null,
            ]);

            $invites[] = [
                'token' => $participant->invite_token,
                'url' => url('/join/' . $participant->invite_token)
            ];
        }

        return response()->json([
            'message' => 'Invites created successfully',
            'invites' => $invites
        ]);
    }

    public function getToken(Room $room)
    {
        Gate::authorize('view', $room);

        try {
            $user = auth()->user();
            $identity = $user->id . '_' . $user->role;
            $permissions = $this->tokenService->getPermissionsForRole($user->role);

            $token = $this->tokenService->generateAccessToken(
                $identity,
                $room->slug,
                $permissions,
                3600 // 1 hour TTL
            );

            return response()->json([
                'token' => $token,
                'room' => $room,
                'server_url' => config('services.livekit.url'),
                'identity' => $identity,
                'permissions' => $permissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate access token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function start(Room $room)
    {
        Gate::authorize('update', $room);

        if ($room->status === 'live') {
            return response()->json([
                'message' => 'Room is already live'
            ], 400);
        }

        $room->update(['status' => 'live']);

        Broadcast::create([
            'room_id' => $room->id,
            'started_at' => now(),
            'type' => 'live',
        ]);

        return response()->json([
            'message' => 'Room started successfully',
            'room' => $room->fresh()
        ]);
    }

    public function stop(Room $room)
    {
        Gate::authorize('update', $room);

        if ($room->status !== 'live') {
            return response()->json([
                'message' => 'Room is not currently live'
            ], 400);
        }

        $room->update(['status' => 'offline']);

        $broadcast = $room->broadcasts()->whereNull('ended_at')->latest()->first();
        if ($broadcast) {
            $broadcast->update(['ended_at' => now()]);
        }

        return response()->json([
            'message' => 'Room stopped successfully',
            'room' => $room->fresh()
        ]);
    }

    public function switch(Room $room, Request $request)
    {
        Gate::authorize('update', $room);

        $validator = Validator::make($request->all(), [
            'source' => 'required|in:camA,camB,clip',
            'clip_id' => 'nullable|exists:uploads,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // In a real implementation, this would switch the video source
        // For now, we'll just return success
        return response()->json([
            'message' => 'Source switched successfully',
            'source' => $request->source,
            'clip_id' => $request->clip_id
        ]);
    }

    public function schedule(Room $room, Request $request)
    {
        Gate::authorize('create', Schedule::class);
        Gate::authorize('update', $room);

        $validator = Validator::make($request->all(), [
            'upload_id' => 'required|exists:uploads,id',
            'start_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $schedule = Schedule::create([
            'room_id' => $room->id,
            'upload_id' => $request->upload_id,
            'start_at' => $request->start_at,
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Schedule created successfully',
            'schedule' => $schedule->load(['upload', 'room'])
        ], 201);
    }
}
