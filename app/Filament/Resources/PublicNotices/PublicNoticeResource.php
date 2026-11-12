<?php

namespace App\Filament\Resources\PublicNotices;

use App\Filament\Resources\PublicNotices\Pages\CreatePublicNotice;
use App\Filament\Resources\PublicNotices\Pages\EditPublicNotice;
use App\Filament\Resources\PublicNotices\Pages\ListPublicNotices;
use App\Filament\Resources\PublicNotices\Schemas\PublicNoticeForm;
use App\Filament\Resources\PublicNotices\Tables\PublicNoticesTable;
use App\Models\PublicNotice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PublicNoticeResource extends Resource
{
    protected static ?string $model = PublicNotice::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Megaphone;

    protected static ?string $recordTitleAttribute = 'title';

    protected static ?string $navigationLabel = 'নোটিশ সমূহ';

    protected static ?string $modelLabel = 'নোটিশ';

    protected static ?string $pluralModelLabel = 'নোটিশসমূহ';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PublicNoticeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicNoticesTable::configure($table);
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
            'index' => ListPublicNotices::route('/'),
            'create' => CreatePublicNotice::route('/create'),
            'edit' => EditPublicNotice::route('/{record}/edit'),
        ];
    }
}
