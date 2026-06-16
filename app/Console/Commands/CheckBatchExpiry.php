<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Batch;
use App\User;
use App\Notifications\BatchExpiryNotification;
use Carbon\Carbon;

class CheckBatchExpiry extends Command
{
    protected $signature = 'batch:check-expiry';
    protected $description = 'Check expired batches and notify admin';

    public function handle()
    {
        $expiredBatches = Batch::with('product')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now())
            ->get();

        if ($expiredBatches->isEmpty()) {
            $this->info('No expired batches found.');
            return;
        }

        $admins = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', 'Admin#%');
        })->get();

        foreach ($admins as $admin) {
            // Aaj already notify hua?
            $alreadyNotified = $admin->notifications()
                ->where('type', 'App\Notifications\BatchExpiryNotification')
                ->whereDate('created_at', Carbon::today())
                ->exists();

            if (!$alreadyNotified) {
                $admin->notify(new BatchExpiryNotification($expiredBatches));
            }
        }

        $this->info('Done! Summary notification sent.');
    }
}
