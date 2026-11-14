<?php

namespace App\Filament\Admin\Resources\Researchers\Tables;

use App\Models\Content\Researcher;
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

class ResearchersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Nama')->searchable()->sortable(),
            TextColumn::make('expertise')->label('Keahlian')->searchable()->limit(40),
            TextColumn::make('research_group')->label('Kelompok')->badge()->formatStateUsing(fn (?string $state) => Researcher::GROUPS[$state] ?? $state),
            IconColumn::make('is_published')->label('Tayang')->boolean(),
        ])->defaultSort('sort_order')->filters([
            SelectFilter::make('research_group')->label('Kelompok')->options(Researcher::GROUPS),
            TrashedFilter::make(),
        ])->recordActions([EditAction::make()])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make(), RestoreBulkAction::make(), ForceDeleteBulkAction::make()]),
        ]);
    }
}
