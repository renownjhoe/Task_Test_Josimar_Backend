<?php

namespace App\Filament\Widgets;

use App\Models\Brt;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;

class TotalReservedAmountWidget extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Total Reserved Amount', number_format(Brt::sum('reserved_amount'), 2)),
        ];
    }
}
