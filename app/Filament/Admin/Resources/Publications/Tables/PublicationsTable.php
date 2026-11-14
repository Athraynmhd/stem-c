<?php

namespace App\Filament\Admin\Resources\Publications\Tables;

use App\Models\Content\Publication;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PublicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->limit(60)->wrap(),
                TextColumn::make('authors')->label('Penulis')->searchable()->limit(35)->toggleable(),
                TextColumn::make('type')->label('Jenis')->badge()
                    ->formatStateUsing(fn (string $state): string => Publication::TYPES[$state] ?? $state),
                TextColumn::make('year')->label('Tahun')->sortable(),
                IconColumn::make('is_published')->label('Tayang')->boolean(),
                IconColumn::make('is_featured')->label('Sorot')->boolean(),
            ])
            ->defaultSort('year', 'desc')
            ->filters([
                SelectFilter::make('type')->label('Jenis')->options(Publication::TYPES),
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
