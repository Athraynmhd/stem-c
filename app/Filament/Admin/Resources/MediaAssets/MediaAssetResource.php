<?php

namespace App\Filament\Admin\Resources\MediaAssets;

use App\Cms\Access\CmsAccess;
use App\Filament\Admin\Resources\MediaAssets\Pages\ListMediaAssets;
use App\Filament\Admin\Resources\MediaAssets\Tables\MediaAssetsTable;
use App\Models\Auth\User;
use App\Models\Cms\MediaAsset;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaAssetResource extends Resource
{
    protected static ?string $model = MediaAsset::class;

    protected static ?string $navigationLabel = 'Pustaka Media';

    protected static ?string $modelLabel = 'Aset Media';

    protected static ?string $pluralModelLabel = 'Pustaka Media';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 95;

    public static function canAccess(): bool
    {
        return CmsAccess::can(auth()->user() instanceof User ? auth()->user() : null, CmsAccess::MEDIA_LIBRARY);
    }

    public static function table(Table $table): Table
    {
        return MediaAssetsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaAssets::route('/'),
        ];
    }
}
