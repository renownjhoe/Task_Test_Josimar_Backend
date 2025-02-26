<?php

namespace App\Filament\Admin\Resources\BrtResource\Pages;

use App\Filament\Admin\Resources\BrtResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBrt extends CreateRecord
{
    protected static string $resource = BrtResource::class;

    protected function afterCreate(): void
    {
        \Log::info('BRT Created:', ['brt' => $this->record]);
        event(new \App\Events\BrtCreated($this->record));
    }

}
