<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Schedule;
use App\Models\Broadcast;
use Illuminate\Support\Facades\Log;

class ProcessScheduledBroadcasts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Get pending schedules that should start now
        $schedules = Schedule::where('status', 'pending')
            ->where('start_at', '<=', now())
            ->with(['room', 'upload'])
            ->get();

        foreach ($schedules as $schedule) {
            try {
                $this->processSchedule($schedule);
            } catch (\Exception $e) {
                Log::error('Failed to process schedule', [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage()
                ]);
                $schedule->update(['status' => 'failed']);
            }
        }

        // Check for schedules that need to end
        $activeSchedules = Schedule::where('status', 'active')
            ->with(['room', 'upload'])
            ->get();

        foreach ($activeSchedules as $schedule) {
            $endTime = $schedule->start_at->addSeconds($schedule->upload->duration_sec);

            if (now()->gte($endTime)) {
                $this->endSchedule($schedule);
            }
        }
    }

    /**
     * Process a single schedule
     */
    protected function processSchedule(Schedule $schedule)
    {
        // Update schedule status
        $schedule->update(['status' => 'active']);

        // Start the room if not already live
        if ($schedule->room->status !== 'live') {
            $schedule->room->update(['status' => 'live']);

            // Create broadcast record
            Broadcast::create([
                'room_id' => $schedule->room->id,
                'started_at' => now(),
                'type' => 'scheduled',
            ]);
        }

        // Trigger video playback via WebRTC/LiveKit
        $this->triggerVideoPlayback($schedule);

        Log::info('Started scheduled broadcast', [
            'schedule_id' => $schedule->id,
            'room_id' => $schedule->room->id,
            'upload_id' => $schedule->upload->id
        ]);
    }

    /**
     * End a scheduled broadcast
     */
    protected function endSchedule(Schedule $schedule)
    {
        // Update schedule status
        $schedule->update(['status' => 'completed']);

        // Check if there are more active schedules for this room
        $activeCount = Schedule::where('room_id', $schedule->room_id)
            ->where('status', 'active')
            ->where('id', '!=', $schedule->id)
            ->count();

        // If no more active schedules, stop the room
        if ($activeCount === 0) {
            $schedule->room->update(['status' => 'offline']);

            // End broadcast record
            $broadcast = Broadcast::where('room_id', $schedule->room_id)
                ->whereNull('ended_at')
                ->latest()
                ->first();

            if ($broadcast) {
                $broadcast->update(['ended_at' => now()]);
            }
        }

        Log::info('Ended scheduled broadcast', [
            'schedule_id' => $schedule->id,
            'room_id' => $schedule->room->id
        ]);
    }

    /**
     * Trigger video playback via API or WebSocket
     */
    protected function triggerVideoPlayback(Schedule $schedule)
    {
        // This would integrate with your WebRTC/LiveKit implementation
        // to actually play the video file

        // For now, we'll just log it
        Log::info('Triggering video playback', [
            'room_slug' => $schedule->room->slug,
            'video_file' => $schedule->upload->stored_filename ?? $schedule->upload->filename,
            'duration' => $schedule->upload->duration_sec
        ]);

        // In production, this might:
        // 1. Send WebSocket message to the control panel
        // 2. Trigger LiveKit API to switch source
        // 3. Start ffmpeg process to stream file
    }
}
