<?php

namespace App\Notifications;

use App\Transaction;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandApprovedNotification extends Notification
{
    use Queueable;

   
    protected $transaction;
    protected $approverUser;

    public function __construct(Transaction $transaction, User $approverUser)
    {
        $this->transaction = $transaction;
        $this->approverUser = $approverUser;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {

        $approverUserName = '<strong>' . $this->approverUser->username . '</strong>';
        
        return [
            'type' => 'demand approved',
            'transaction_id' => $this->transaction->id,
            'status' => $this->transaction->status,
            'message' => 'Your demand request has been approved by ' . $approverUserName,
            'demand_message' => 'Your demand request has been successfully approved.',
        ];
    }
    
}
