<?php

namespace App\Filament\Admin\Resources\MediaAssets\Pages;

use App\Filament\Admin\Resources\MediaAssets\MediaAssetResource;
use Filament\Resources\Pages\ListRecords;

class ListMediaAssets extends ListRecords
{
    protected static string $resource = MediaAssetResource::class;
}
