<?php

namespace App\Events;

use App\STRRemarks;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RemarkCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $remark;

    public function __construct(STRRemarks $remark)
    {
        $this->remark = $remark;
    }
    /**
     * Create a new event instance.
     *
     * @return void
     */
   

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
