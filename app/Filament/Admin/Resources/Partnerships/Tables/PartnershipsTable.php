<?php

namespace App\Filament\Admin\Resources\Partnerships\Tables;

use App\Models\Content\Partnership;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PartnershipsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('partner_name')->label('Mitra')->searchable()->sortable(),
            TextColumn::make('title')->label('Judul')->searchable()->limit(55)->wrap(),
            TextColumn::make('type')->label('Jenis')->badge()->formatStateUsing(fn (string $state) => Partnership::TYPES[$state] ?? $state),
            TextColumn::make('started_on')->label('Mulai')->date('d M Y')->sortable(),
            TextColumn::make('ended_on')->label('Berakhir')->date('d M Y'),
            IconColumn::make('is_active')->label('Aktif')->boolean(),
            IconColumn::make('is_published')->label('Tayang')->boolean(),
        ])->defaultSort('started_on', 'desc')->filters([SelectFilter::make('type')->options(Partnership::TYPES)])
            ->recordActions([EditAction::make()])->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
