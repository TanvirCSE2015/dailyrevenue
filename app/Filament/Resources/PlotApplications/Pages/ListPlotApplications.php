<?php

namespace App\Filament\Resources\PlotApplications\Pages;

use App\Filament\Resources\PlotApplications\PlotApplicationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPlotApplications extends ListRecords
{
    protected static string $resource = PlotApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
