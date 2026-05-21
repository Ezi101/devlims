<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete notifications older than one week and keep only the recent ones.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $oneWeekAgo =Carbon::now()->subDays(3);

        $this->info("Cleaning up notifications older than: {$oneWeekAgo}");

        DB::table('notifications')
            ->where('created_at', '<', $oneWeekAgo)
            ->delete();

        $this->info('Old notifications deleted successfully!');
    }
}
