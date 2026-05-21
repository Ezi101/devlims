<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemarkNotification extends Notification
{
    use Queueable;

    protected $remark;
    

    public function __construct($remark)
    {
        $this->remark = $remark;
    }

    public function via($notifiable)
    {
        
        return ['database'];
    }
    

    public function toArray($notifiable)
    {
        $senderFullName = '<strong>' . $this->remark->remarkBy->surname . ' ' . $this->remark->remarkBy->first_name . ' ' . $this->remark->remarkBy->last_name . '</strong>';
        
        return [
            'type' => 'remark',
            'remark_id' => $this->remark->id,
            'message' => 'A new remark ' . $this->remark->str_no . 'has been added by ' . $senderFullName,
            'str_no' => $this->remark->str_no,
            'remark_message' => $this->remark->remark,
        ];
    }
}
