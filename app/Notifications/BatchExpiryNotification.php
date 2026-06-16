<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class BatchExpiryNotification extends Notification
{
    protected $batches;

    public function __construct($batches)
    {
        $this->batches = $batches;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type'    => 'batch_expiry',
            'message' => '⚠️ <strong>' . count($this->batches) .
                ' batches</strong> have expired. Please review.',
            'url'     => url('batch/expired'),
        ];
    }
}
