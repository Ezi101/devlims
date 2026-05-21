<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandNotification extends Notification
{
    use Queueable;
    protected $demand;
    protected $user;

    public function __construct($demand, $user)
    {
        $this->demand = $demand;
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $senderFullName = '<strong>' . $this->user->surname . ' ' . $this->user->first_name . ' ' . $this->user->last_name . '</strong>';
        
        return [
            'type' => 'demand',
            'demand_id' => $this->demand->id,
            'message' => 'A new demand request has been added by ' . $senderFullName,
            'product_id' => $this->demand->product_id,
            'quantity' => $this->demand->quantity,
            'product_type' => $this->demand->product_type,
            'demand_message' => 'Your demand request has been successfully stored.',
        ];
    }
    
}
