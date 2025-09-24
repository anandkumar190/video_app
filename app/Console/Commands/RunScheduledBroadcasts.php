<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessScheduledBroadcasts;
use Illuminate\Support\Facades\Log;

class RunScheduledBroadcasts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'broadcasts:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled broadcasts and manage their lifecycle';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Processing scheduled broadcasts...');

        try {
            // Dispatch the job to process scheduled broadcasts
            ProcessScheduledBroadcasts::dispatch();

            $this->info('Scheduled broadcast processing job dispatched successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to process scheduled broadcasts: ' . $e->getMessage());
            Log::error('Command failed: broadcasts:process-scheduled', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        }
    }
}
