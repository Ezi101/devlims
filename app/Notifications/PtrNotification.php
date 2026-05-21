<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PtrNotification extends Notification
{
    use Queueable;

    protected $ptr_no;
    protected $remark_by;

    public function __construct($ptr_no, $remark_by)
    {
        $this->ptr_no = $ptr_no;
        $this->remark_by = $remark_by;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'ptr_approved',
            'ptr_no' => $this->ptr_no,
            'approver_name' => $this->remark_by,
            'message' =>  $this->ptr_no . ' has been Forwarded by ' . auth()->user()->first_name . ' ' . auth()->user()->last_name,
        ];
    }
}
