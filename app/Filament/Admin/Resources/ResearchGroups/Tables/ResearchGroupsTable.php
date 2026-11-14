<?php

namespace App\Filament\Admin\Resources\ResearchGroups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ResearchGroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('code')->label('Kode')->badge(),
            TextColumn::make('researchers_count')->counts('researchers')->label('Peneliti'),
            IconColumn::make('is_published')->label('Tayang')->boolean(),
        ])->defaultSort('sort_order')->filters([
            TrashedFilter::make(),
        ])->recordActions([EditAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make(), ForceDeleteBulkAction::make()]),
        ]);
    }
}
