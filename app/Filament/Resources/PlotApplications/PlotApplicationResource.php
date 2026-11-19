<?php

namespace App\Filament\Resources\PlotApplications;

use App\Filament\Resources\PlotApplications\Pages\CreatePlotApplication;
use App\Filament\Resources\PlotApplications\Pages\EditPlotApplication;
use App\Filament\Resources\PlotApplications\Pages\ListPlotApplications;
use App\Filament\Resources\PlotApplications\Schemas\PlotApplicationForm;
use App\Filament\Resources\PlotApplications\Tables\PlotApplicationsTable;
use App\Models\PlotApplication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PlotApplicationResource extends Resource
{
    protected static ?string $model = PlotApplication::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Document;

    protected static ?string $recordTitleAttribute = 'application_no';

    protected static ?string $navigationLabel = 'আবেদন';

    protected static ?string $modelLabel = 'আবেদন';

    protected static ?string $pluralModelLabel = 'আবেদনসমূহ';

    protected static string | UnitEnum | null $navigationGroup = 'আবেদন ব্যবস্থাপনা';

    public static function form(Schema $schema): Schema
    {
        return PlotApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PlotApplicationsTable::configure($table);
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
            'index' => ListPlotApplications::route('/'),
            'create' => CreatePlotApplication::route('/create'),
            'edit' => EditPlotApplication::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
