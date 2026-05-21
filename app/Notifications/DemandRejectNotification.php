<?php

namespace App\Notifications;

use App\Transaction;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandRejectNotification extends Notification
{
    use Queueable;

    protected $transaction;
    protected $rejectingUser;

    public function __construct(Transaction $transaction, User $rejectingUser)
    {
        $this->transaction = $transaction;
        $this->rejectingUser = $rejectingUser;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {

        $rejectingUserName = '<strong>' . $this->rejectingUser->username . '</strong>';
        
        return [
            'type' => 'demand reject',
            'transaction_id' => $this->transaction->id,
            'status' => $this->transaction->status,
            'message' => 'A demand request has been rejected by ' . $rejectingUserName,
            'demand_message' => 'Your demand request has been successfully rejected.',
        ];
    }
    
}
