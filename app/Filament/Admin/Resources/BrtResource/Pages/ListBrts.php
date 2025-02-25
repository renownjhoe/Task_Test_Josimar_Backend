<?php

namespace App\Filament\Admin\Resources\BrtResource\Pages;

use App\Filament\Admin\Resources\BrtResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBrts extends ListRecords
{
    protected static string $resource = BrtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function afterDelete(): void
    {
        event(new \App\Events\BrtDeleted($this->record));
    }
}
