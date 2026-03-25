<?php

namespace App\Console;

use App\Services\PowithetaScheduleSettings;
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
        $config = PowithetaScheduleSettings::get();
        if (! ($config['enabled'] ?? true)) {
            return;
        }

        foreach (PowithetaScheduleSettings::normalizedSyncTimes() as $time) {
            $schedule->command('powitheta:refresh-from-sap --scheduled')
                ->dailyAt($time)
                ->withoutOverlapping(20);

            if ($config['staging_modules_enabled'] ?? true) {
                $schedule->command('staging-modules:sync-from-sap --scheduled')
                    ->dailyAt($time)
                    ->withoutOverlapping(25);
            }
        }
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
