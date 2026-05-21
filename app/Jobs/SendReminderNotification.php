<?php
namespace App\Jobs;

use App\Models\User;
use App\Notifications\ReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendReminderNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $reminder;

    public function __construct(AuthUser $user, $reminder)
    {
        $this->user = $user;
        $this->reminder = $reminder;
    }

    public function handle()
    {
        $this->user->notify(new ReminderNotification($this->reminder));
    }
}
