<?php

namespace App\Filament\Admin\Resources\MediaAssets\Tables;

use App\Models\Cms\MediaAsset;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaAssetsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('path')->label('Pratinjau')->disk('public')->square(),
            TextColumn::make('original_name')->label('Nama')->searchable()->limit(40),
            TextColumn::make('folder')->label('Folder')->badge(),
            TextColumn::make('mime')->label('MIME')->toggleable(),
            TextColumn::make('size')->label('Ukuran')->formatStateUsing(fn (?int $state): string => $state ? number_format($state / 1024, 1).' KB' : '—'),
            TextColumn::make('checksum')->label('SHA-256')->limit(12)->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')->label('Unggah')->dateTime('d M Y H:i')->sortable(),
        ])->defaultSort('created_at', 'desc')->filters([
            SelectFilter::make('folder')->label('Folder')->options(
                fn () => MediaAsset::query()->whereNotNull('folder')->distinct()->orderBy('folder')->pluck('folder', 'folder')->all()
            ),
        ]);
    }
}
