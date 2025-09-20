<?php

namespace App\Console;

use App\Jobs\CleanupDownloadsJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Clean up old download files every hour
        $schedule->job(new CleanupDownloadsJob())
            ->hourly()
            ->name('cleanup-downloads')
            ->withoutOverlapping()
            ->onOneServer();

        // Alternative: You can also run it as a command
        // $schedule->command('downloader:cleanup')->hourly();

        // Optional: Clean up failed jobs from the queue
        $schedule->command('queue:prune-failed --hours=48')
            ->daily()
            ->at('02:00');

        // Optional: Clear application cache daily
        $schedule->command('cache:clear')
            ->daily()
            ->at('03:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}