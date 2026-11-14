<?php

namespace App\Filament\Admin\Resources\LoginActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoginActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            TextColumn::make('event')->label('Peristiwa')->badge()
                ->color(fn (string $state): string => match ($state) {
                    'login' => 'success',
                    'logout' => 'gray',
                    'failed' => 'danger',
                    default => 'gray',
                }),
            TextColumn::make('user_name')->label('Pengguna')->searchable(),
            TextColumn::make('email')->label('Email')->searchable(),
            TextColumn::make('ip')->label('IP'),
            TextColumn::make('user_agent')->label('Peramban')->limit(30)->toggleable(isToggledHiddenByDefault: true),
        ])->defaultSort('created_at', 'desc')->filters([
            SelectFilter::make('event')->label('Peristiwa')->options([
                'login' => 'Login',
                'logout' => 'Logout',
                'failed' => 'Gagal',
            ]),
        ]);
    }
}
