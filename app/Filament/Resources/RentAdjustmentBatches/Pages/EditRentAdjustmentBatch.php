<?php

namespace App\Filament\Resources\RentAdjustmentBatches\Pages;

use App\Filament\Resources\RentAdjustmentBatches\RentAdjustmentBatchResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRentAdjustmentBatch extends EditRecord
{
    protected static string $resource = RentAdjustmentBatchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
