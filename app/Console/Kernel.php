<?php

namespace App\Console;

use App\Models\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            Notification::query()
                ->whereNotNull('notification_publish_date')
                ->whereDate('created_at', '<=', now()->subDays(90))
                ->delete();
        })->daily();

        $schedule->command('command:create-fiscal-year')
            ->dailyAt('05:00');

        $schedule->command('command:update-transferred-user')
            ->dailyAt('05:15');
        $schedule->command('command:update-promoted-user')
            ->dailyAt('05:30');

        $schedule->command('command:disable-user')
            ->dailyAt('06:15');

        $schedule->command('command:disable-terminated-user')
            ->dailyAt('06:20');

        $schedule->command('command:holiday-notification')
            ->dailyAt('07:00');

        $schedule->command('command:birthday-notification')
            ->dailyAt('07:05');

        $schedule->command('command:award-notification')
            ->dailyAt('07:10');

        $schedule->command('command:update-project-status')
            ->dailyAt('07:40');





    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
