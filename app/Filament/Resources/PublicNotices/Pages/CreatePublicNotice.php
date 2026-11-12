<?php

namespace App\Filament\Resources\PublicNotices\Pages;

use App\Filament\Resources\PublicNotices\PublicNoticeResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublicNotice extends CreateRecord
{
    protected static string $resource = PublicNoticeResource::class;
}
