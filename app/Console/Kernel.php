<?php

namespace App\Console;

use App\Services\PowithetaScheduleSettings;
use Carbon\Carbon;
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
        $schedule->command('history:generate-monthly')
            ->dailyAt('23:45')
            ->when(static function (): bool {
                return now()->day === now()->daysInMonth;
            })
            ->withoutOverlapping(60);

        $config = PowithetaScheduleSettings::get();
        if (! ($config['enabled'] ?? true)) {
            return;
        }

        foreach (['06:05', '12:05'] as $time) {
            $schedule->command('powitheta:refresh-from-sap --scheduled')
                ->dailyAt($time)
                ->withoutOverlapping(20);

            if ($config['staging_modules_enabled'] ?? true) {
                $stagingAt = Carbon::createFromFormat('H:i', $time)
                    ->addMinutes(5)
                    ->format('H:i');
                $schedule->command('staging-modules:sync-from-sap --scheduled')
                    ->dailyAt($stagingAt)
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
