<?php

namespace App\Filament\Admin\Resources\BrtResource\Widgets;

use Filament\Widgets\ChartWidget;

class TotalReservedAmountWidget extends ChartWidget
{
    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {
        return [
            //
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
