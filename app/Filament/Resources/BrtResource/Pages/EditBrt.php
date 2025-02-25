<?php

namespace App\Filament\Resources\BrtResource\Pages;

use App\Filament\Resources\BrtResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBrt extends EditRecord
{
    protected static string $resource = BrtResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
