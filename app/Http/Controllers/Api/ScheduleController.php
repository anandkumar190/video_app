<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Schedule;
use Illuminate\Support\Facades\Gate;

class ScheduleController extends Controller
{
    public function cancel(Schedule $schedule)
    {
        Gate::authorize('delete', $schedule);

        if ($schedule->status === 'running') {
            return response()->json([
                'message' => 'Cannot cancel a running schedule'
            ], 400);
        }

        if ($schedule->status === 'done' || $schedule->status === 'canceled') {
            return response()->json([
                'message' => 'Schedule is already completed or canceled'
            ], 400);
        }

        $schedule->update(['status' => 'canceled']);

        return response()->json([
            'message' => 'Schedule canceled successfully',
            'schedule' => $schedule->load(['upload', 'room'])
        ]);
    }
}
