<?php

namespace App\Filament\Admin\Resources\MediaItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MediaItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('Pratinjau')->square(),
                TextColumn::make('title')->label('Judul')->searchable()->limit(45)->wrap(),
                TextColumn::make('album')->label('Album')->badge()->searchable(),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->formatStateUsing(fn (string $state): string => $state === 'video' ? 'Video' : 'Foto')
                    ->color(fn (string $state): string => $state === 'video' ? 'warning' : 'info'),
                TextColumn::make('captured_at')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                IconColumn::make('is_published')->label('Tayang')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options([
                    'photo' => 'Foto',
                    'video' => 'Video',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
