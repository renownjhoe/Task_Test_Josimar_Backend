<?php

namespace App\Observers;

use App\Models\Brt;
use App\Events\BrtCreated;
use App\Events\BrtUpdated;
use App\Events\BrtDeleted;

class BrtObserver
{
    public function created(Brt $brt): void
    {
        event(new BrtCreated($brt));
    }

    public function updated(Brt $brt): void
    {
        event(new BrtUpdated($brt));
    }

    public function deleted(Brt $brt): void
    {
        event(new BrtDeleted($brt));
    }

    /**
     * Handle the Brt "restored" event.
     */
    public function restored(Brt $brt): void
    {
        //
    }

    /**
     * Handle the Brt "force deleted" event.
     */
    public function forceDeleted(Brt $brt): void
    {
        //
    }
}
