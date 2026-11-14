<?php

namespace App\Filament\Admin\Resources\Documents\Tables;

use App\Models\Content\Document;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Judul')->searchable()->limit(50)->wrap(),
                TextColumn::make('category')->label('Kategori')->badge()
                    ->formatStateUsing(fn (string $state): string => Document::CATEGORIES[$state] ?? $state),
                TextColumn::make('extension')->label('Format')->badge()->color('gray'),
                TextColumn::make('size_label')->label('Ukuran')->placeholder('-'),
                TextColumn::make('published_on')->label('Terbit')->date('d M Y')->sortable()->placeholder('-'),
                TextColumn::make('sort_order')->label('Urutan')->sortable(),
                IconColumn::make('is_published')->label('Tayang')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                SelectFilter::make('category')->label('Kategori')->options(Document::CATEGORIES),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }
}
