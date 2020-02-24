<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\ApplicationVersion;
use App\Console\Commands\Maintenance;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Maintenance\SetVersionAllMusic::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
            $v = new ApplicationVersion();
            $v->fetchAllVersion();
        })->everyFiveMinutes();

        // 全ユーザーの月初課金情報初期化
        $schedule->call(function () {
            \App\UserInformation::ResetAllUserPaymentState();
        })->monthlyOn(1, '4:00');
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
