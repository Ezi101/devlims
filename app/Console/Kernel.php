<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use App\Http\Controllers\WhatsAppController;
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
        // $env = config('app.env');
        // $email = config('mail.username');

        // if ($env === 'live') {
        //     //Scheduling backup, specify the time when the backup will get cleaned & time when it will run.
        //     $schedule->command('backup:run')->dailyAt('23:50');

        //     //Schedule to create recurring invoices
        //     $schedule->command('pos:generateSubscriptionInvoices')->dailyAt('23:30');
        //     $schedule->command('pos:updateRewardPoints')->dailyAt('23:45');

        //     $schedule->command('pos:autoSendPaymentReminder')->dailyAt('8:00');
        // }

        // if ($env === 'demo') {
        //     //IMPORTANT NOTE: This command will delete all business details and create dummy business, run only in demo server.
        //     $schedule->command('pos:dummyBusiness')
        //         ->cron('0 */3 * * *')
        //         //->everyThirtyMinutes()
        //         ->emailOutputTo($email);
        // }

        // $schedule->command('notifications:cleanup')->everyweek();
        $schedule->command('notifications:cleanup')->weekly()->fridays()->at('20:00');
        $schedule->command('batch:check-expiry')->dailyAt('08:00');

        $schedule->call(function () {
            app(WhatsAppController::class)->sendMessage();
        })->dailyAt('07:00');
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}