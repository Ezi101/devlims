<?php

namespace App\Notifications;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InboxNotification extends Notification
{
    use Queueable;
    protected $inbox;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($inbox)
    {
       $this->inbox = $inbox;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $sender = User::find($this->inbox->message_from);
        
        $senderFullName = '<strong>' . $sender->surname . ' ' . $sender->first_name . ' ' . $sender->last_name . '</strong>';
        
        return [
            'type' => 'inbox',
            'remark_by_id' => $this->inbox->message_from, 
            'message' => 'A new message is received from ' . $senderFullName,
            'remark_to_id' => $this->inbox->message_to,
        ];
    }
}


