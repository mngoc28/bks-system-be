<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Partner Portal 360 Phase 5 — daily renewal-reminder pass at 06:00 (Asia/Ho_Chi_Minh).
        $schedule->command('partner:send-contract-renewal-reminders')
            ->dailyAt('06:00')
            ->timezone('Asia/Ho_Chi_Minh')
            ->withoutOverlapping()
            ->onOneServer();

        // Admin Revenue Reconciliation — run on 1st and 16th at 01:00 AM to generate settlement periods
        $schedule->job(new \App\Jobs\GenerateSettlementPeriodsJob())
            ->cron('0 1 1,16 * *')
            ->timezone('Asia/Ho_Chi_Minh')
            ->withoutOverlapping();

        // Auto cancel unpaid bookings past grace period — run every 10 minutes
        $schedule->job(new \App\Jobs\CancelExpiredUnpaidBookingsJob())
            ->everyTenMinutes()
            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . "/Commands");

        require base_path("routes/console.php");
    }
}
