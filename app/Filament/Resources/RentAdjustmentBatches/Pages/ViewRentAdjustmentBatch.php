<?php

namespace App\Filament\Resources\RentAdjustmentBatches\Pages;

use App\Filament\Resources\RentAdjustmentBatches\RentAdjustmentBatchResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRentAdjustmentBatch extends ViewRecord
{
    protected static string $resource = RentAdjustmentBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
