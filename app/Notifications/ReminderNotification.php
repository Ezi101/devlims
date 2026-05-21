<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $reminder;

    public function __construct($reminder)
    {
        $this->reminder = $reminder;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'reminder',
            'message' => 'You have a reminder: ' . $this->reminder['date'] . ' at ' . $this->reminder['end_time'],
            'reminder_id' => $this->reminder['id'],
            'time' => $this->reminder['end_time'],
            'date' => $this->reminder['date'],
           'sound' => asset('audio/success.mp3'),
        ];
    }
}
