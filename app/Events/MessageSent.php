<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MessageSent  implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $userId;

    public $message;

    private $type;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userId,$message,$type)
    {
        $this->userId = $userId;
        $this->message = $message;
        $this->type=$type;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('chat.group.'.$this->userId.'.'.$this->type);
//        return new PrivateChannel('chat.group');
    }


    /**
     * 指定广播事件(对应前端的事件)
     * @return string
     */
    public function broadcastAs()
    {
        return 'new-message';
    }
}
