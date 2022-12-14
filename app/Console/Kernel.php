<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        "App\Console\Commands\Download",
        "App\Console\Commands\FileDownload",
        "App\Console\Commands\FillFiles",
        "App\Console\Commands\UpdateCallInfo",
        "App\Console\Commands\ResumingFiles"
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('download')->everyThirtyMinutes();
        $schedule->command('resumingFiles')->dailyAt("00:00:00");
    }
}
