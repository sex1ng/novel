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
    protected $commands = [
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /**
        $schedule->call(function () {
            //php语句
        })->daily()->after(function () {
            //上面执行后执行
        });;

        $schedule->command('cron:template')->dailyAt("01:00");
         */
        // 线上环境只在单点服务器上执行。
//        if (app()->environment() !== 'production' || $this->isSingle()) {
//
//        }
    }

    /**
     * 判断当前是否是单点服务器
     */
    protected function isSingle()
    {
        return gethostname() == config('server.single');
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
