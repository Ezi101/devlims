<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestApprovalNotification extends Notification
{
    use Queueable;
    protected $tests_name;
    protected $ptrNo;
    protected $approverUser;

    public function __construct($tests_name, $ptrNo, $approverUser)
    {
        $this->tests_name = $tests_name;
        $this->ptrNo = $ptrNo;
        $this->approverUser = $approverUser;
    }

    public function via($notifiable)
    {
        return [ 'database']; 
    }

  

    public function toArray($notifiable)
    {
        return [
            'type' => 'test_approve',
            'message' => $this->tests_name . ' ' .'has been approved by ' . $this->approverUser->getuser,
            'test' => $this->tests_name,
            'ptr_no' => $this->ptrNo,
            'approved_by' => $this->approverUser->name,
        ];
    }
}
