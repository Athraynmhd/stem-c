<?php

namespace App\Filament\Admin\Resources\LoginActivityLogs\Pages;

use App\Filament\Admin\Resources\LoginActivityLogs\LoginActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListLoginActivityLogs extends ListRecords
{
    protected static string $resource = LoginActivityLogResource::class;
}
