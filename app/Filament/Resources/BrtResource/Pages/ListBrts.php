<?php

namespace App\Filament\Resources\BrtResource\Pages;

use App\Filament\Resources\BrtResource;
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
}
