<?php

namespace App\Events;

use App\Models\Brt;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class BrtDeleted implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $brt;

    public function __construct(Brt $brt)
    {
        $this->brt = $brt;
    }

    public function broadcastOn()
    {
        return new Channel('brts');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->brt->id,
            'brt_code' => $this->brt->brt_code,
        ];
    }

    public function broadcastAs()
    {
        return 'brt.deleted';
    }
}
