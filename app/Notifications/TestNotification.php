<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification
{
    use Queueable;

    protected $tests_name;
    protected $ptrNo;
    protected $assignedBy;
    protected $issue_id;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($tests_name, $ptrNo, $assignedBy,$issue_id)
    {
        $this->tests_name = $tests_name;
        $this->ptrNo = $ptrNo;
        $this->assignedBy = $assignedBy;
        $this->issue_id = $issue_id;
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
            'type' => 'assign_test',
            'message'  =>   'A new test has been assigned by '.'<strong>'  . $this->assignedBy->first_name  . $this->assignedBy->last_name .'</strong>',
            'test' => $this->tests_name,
            'issue_id'=>$this->issue_id,
           
        ];
    }
}
