<?php

namespace App\Events;

use App\Models\Brt;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BrtCreated implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels, Dispatchable;

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
            'reserved_amount' => $this->brt->reserved_amount,
            'status' => $this->brt->status,
        ];
    }

    public function broadcastAs()
    {
        return 'brt.created';
    }
}
