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
            'type' => 'form_test',
            'message' => '<strong>' . $this->tests_name  . '</strong>'. ' ' .' has been performed by ' .'<strong>'  . $this->approverUser->first_name. $this->approverUser->last_name. '</strong>',
            'test' => $this->tests_name,
            'ptr_no' => $this->ptrNo,
            'approved_by' => $this->approverUser->name,
        ];
    }
}
