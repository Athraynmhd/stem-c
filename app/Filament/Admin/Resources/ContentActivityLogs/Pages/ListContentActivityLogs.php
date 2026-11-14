<?php

namespace App\Filament\Admin\Resources\ContentActivityLogs\Pages;

use App\Filament\Admin\Resources\ContentActivityLogs\ContentActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListContentActivityLogs extends ListRecords
{
    protected static string $resource = ContentActivityLogResource::class;
}
