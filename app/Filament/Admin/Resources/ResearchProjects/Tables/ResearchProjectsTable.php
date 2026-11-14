<?php

namespace App\Filament\Admin\Resources\ResearchProjects\Tables;

use App\Models\Content\ResearchProject;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ResearchProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(60),
            TextColumn::make('year')->label('Tahun')->sortable(),
            TextColumn::make('leader')->label('Ketua')->searchable(),
            TextColumn::make('status')->label('Status')->badge()->formatStateUsing(fn (string $state) => ResearchProject::STATUSES[$state] ?? $state),
            IconColumn::make('is_published')->label('Tayang')->boolean(),
        ])->defaultSort('year', 'desc')->filters([SelectFilter::make('status')->options(ResearchProject::STATUSES)])
            ->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
