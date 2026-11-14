<?php

namespace App\Filament\Admin\Resources\ContentActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            TextColumn::make('user_name')->label('Pengguna')->searchable(),
            TextColumn::make('action')->label('Aksi')->badge(),
            TextColumn::make('subject_label')->label('Konten')->searchable()->limit(50)->wrap(),
            TextColumn::make('subject_type')->label('Tipe')->formatStateUsing(fn (string $state) => class_basename($state)),
            TextColumn::make('ip')->label('IP'),
        ])->defaultSort('created_at', 'desc');
    }
}
