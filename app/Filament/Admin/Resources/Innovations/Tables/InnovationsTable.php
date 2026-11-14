<?php

namespace App\Filament\Admin\Resources\Innovations\Tables;

use App\Models\Content\Innovation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InnovationsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Judul')->searchable()->wrap()->limit(60),
            TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn (string $state) => Innovation::TYPES[$state] ?? $state),
            TextColumn::make('year')->label('Tahun')->sortable(),
            TextColumn::make('registration_number')->label('Nomor')->toggleable(),
            IconColumn::make('is_featured')->label('Sorot')->boolean(),
            IconColumn::make('is_published')->label('Tayang')->boolean(),
        ])->defaultSort('year', 'desc')->filters([SelectFilter::make('type')->label('Jenis')->options(Innovation::TYPES)])
            ->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
