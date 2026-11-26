<?php

namespace App\Filament\Resources\RentAdjustmentBatches\Pages;

use App\Filament\Resources\RentAdjustmentBatches\RentAdjustmentBatchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRentAdjustmentBatches extends ListRecords
{
    protected static string $resource = RentAdjustmentBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
