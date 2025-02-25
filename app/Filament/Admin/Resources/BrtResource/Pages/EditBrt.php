<?php

namespace App\Filament\Admin\Resources\BrtResource\Pages;

use App\Filament\Admin\Resources\BrtResource;
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
    protected function afterUpdate(): void
    {
        event(new \App\Events\BrtUpdated($this->record));
    }

}
