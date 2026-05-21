<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class STRApproved extends Notification
{
    use Queueable;

    protected $str_no;
    protected $approver_name;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($str_no, $approver_name)
    {
       $this->str_no = $str_no;
       $this->approver_name = $approver_name;
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
        return [
            'type' => 'approve',
            'message' => 'STR #' . '<strong>' . $this->str_no . '</strong>' . ' has been approved by ' . '<strong>' . $this->approver_name . '</strong>',
            'str_no' => $this->str_no,
        ];
    }
}
