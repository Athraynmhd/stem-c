<?php

namespace App\Filament\Admin\Resources\MediaItems;

use App\Filament\Admin\Resources\MediaItems\Pages\CreateMediaItem;
use App\Filament\Admin\Resources\MediaItems\Pages\EditMediaItem;
use App\Filament\Admin\Resources\MediaItems\Pages\ListMediaItems;
use App\Filament\Admin\Resources\MediaItems\Schemas\MediaItemForm;
use App\Filament\Admin\Resources\MediaItems\Tables\MediaItemsTable;
use App\Models\Content\MediaItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MediaItemResource extends Resource
{
    protected static ?string $model = MediaItem::class;

    protected static ?string $navigationLabel = 'Dokumentasi';

    protected static ?string $modelLabel = 'Dokumentasi';

    protected static ?string $pluralModelLabel = 'Dokumentasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return MediaItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MediaItemsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaItems::route('/'),
            'create' => CreateMediaItem::route('/create'),
            'edit' => EditMediaItem::route('/{record}/edit'),
        ];
    }
}
