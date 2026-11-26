<?php

namespace App\Filament\Resources\RentAdjustmentBatches;

use App\Filament\Resources\RentAdjustmentBatches\Pages\CreateRentAdjustmentBatch;
use App\Filament\Resources\RentAdjustmentBatches\Pages\EditRentAdjustmentBatch;
use App\Filament\Resources\RentAdjustmentBatches\Pages\ListRentAdjustmentBatches;
use App\Filament\Resources\RentAdjustmentBatches\Pages\ViewRentAdjustmentBatch;
use App\Filament\Resources\RentAdjustmentBatches\Schemas\RentAdjustmentBatchForm;
use App\Filament\Resources\RentAdjustmentBatches\Schemas\RentAdjustmentBatchInfolist;
use App\Filament\Resources\RentAdjustmentBatches\Tables\RentAdjustmentBatchesTable;
use App\Models\RentAdjustmentBatch;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RentAdjustmentBatchResource extends Resource
{
    protected static ?string $model = RentAdjustmentBatch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

      // Navigation
    protected static ?string $navigationLabel = 'ভাড়া মওকুফ';

    protected static string | UnitEnum | null $navigationGroup = 'দৈনিক ভাড়া ব্যবস্থাপনা';

    protected static ?int $navigationSort = 5;

    // Model Labels
    protected static ?string $modelLabel = 'ভাড়া মওকুফ';

    protected static ?string $pluralModelLabel = 'ভাড়া মওকুফসমূহ';

    public static function form(Schema $schema): Schema
    {
        return RentAdjustmentBatchForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RentAdjustmentBatchInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RentAdjustmentBatchesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRentAdjustmentBatches::route('/'),
            'create' => CreateRentAdjustmentBatch::route('/create'),
            'view' => ViewRentAdjustmentBatch::route('/{record}'),
            'edit' => EditRentAdjustmentBatch::route('/{record}/edit'),
        ];
    }
}
