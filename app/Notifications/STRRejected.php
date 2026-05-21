<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class STRRejected extends Notification
{
    use Queueable;
    
    protected $str_no;
    protected $rejector_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($str_no, $rejector_name)
    {
        $this->str_no = $str_no;
        $this->rejector_name = $rejector_name;
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
     * Get the array representation of the notific+ation.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'reject',
            'message' => 'Your STR #' . $this->str_no . ' has been rejected by ' . $this->rejector_name,
            'str_no' => $this->str_no,
            'rejector_name' => $this->rejector_name,
        ];
    }
}
