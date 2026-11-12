<?php

namespace App\Filament\Resources\PublicNotices\Pages;

use App\Filament\Resources\PublicNotices\PublicNoticeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPublicNotice extends EditRecord
{
    protected static string $resource = PublicNoticeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
