<?php

namespace App\Filament\Admin\Resources\MediaEditLogs\Pages;

use App\Filament\Admin\Resources\MediaEditLogs\MediaEditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListMediaEditLogs extends ListRecords
{
    protected static string $resource = MediaEditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
