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
        // 全ユーザーの月初課金情報初期化
        $schedule->call(function () {
            \App\UserInformation::ResetAllUserPaymentState();
            \App\Facades\Slack::Info("<!here> すべてのユーザーの課金情報をリセットしました。");
        })->monthlyOn(1, '7:00');
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
