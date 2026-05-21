<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StrCreatedNotification extends Notification
{
    use Queueable;

    protected $str_no;
    protected $remark_by;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($str_no, $remark_by)
    {
//        dd($str_no);
        $this->str_no = $str_no;
        $this->remark_by = $remark_by;
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
            'type' => 'str_created',
            'str_no' => $this->str_no,
            'approver_name' => $this->remark_by,
            'message' =>  $this->str_no . ' has been Forwarded by ' . auth()->user()->first_name . ' ' . auth()->user()->last_name,
        ];
    }



}
