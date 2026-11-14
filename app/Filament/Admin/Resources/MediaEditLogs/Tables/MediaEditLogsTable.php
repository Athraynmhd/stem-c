<?php

namespace App\Filament\Admin\Resources\MediaEditLogs\Tables;

use App\Cms\Chrome\SiteContent;
use App\Cms\Media\EditableImage;
use App\Models\Auth\User;
use App\Models\Cms\MediaEditLog;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Throwable;

class MediaEditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('user_name')->label('Pelaku')->searchable(),
                TextColumn::make('target')->label('Target')->badge()->searchable(),
                TextColumn::make('action')->label('Aksi')->badge()
                    ->color(fn (string $state): string => $state === MediaEditLog::ACTION_RESTORE ? 'warning' : 'gray')
                    ->formatStateUsing(fn (string $state): string => $state === MediaEditLog::ACTION_RESTORE ? 'Pulihkan' : 'Ganti'),
                TextColumn::make('old_path')->label('Sebelum')->placeholder('—')
                    ->url(fn (MediaEditLog $record): ?string => $record->old_path ? SiteContent::assetUrl($record->old_path) : null)
                    ->openUrlInNewTab()
                    ->limit(30)->tooltip(fn (MediaEditLog $record): ?string => $record->old_path),
                TextColumn::make('new_path')->label('Sesudah')
                    ->url(fn (MediaEditLog $record): string => SiteContent::assetUrl($record->new_path))
                    ->openUrlInNewTab()
                    ->limit(30)->tooltip(fn (MediaEditLog $record): string => $record->new_path),
                TextColumn::make('ip')->label('IP')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_agent')->label('Peramban')->limit(25)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('action')->label('Aksi')->options([
                    MediaEditLog::ACTION_REPLACE => 'Ganti',
                    MediaEditLog::ACTION_RESTORE => 'Pulihkan',
                ]),
            ])
            ->recordActions([
                Action::make('pulihkan')
                    ->label('Pulihkan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Pulihkan gambar sebelumnya?')
                    ->modalDescription('Gambar target akan dikembalikan ke berkas sebelum penggantian ini. Tindakan ini juga tercatat di riwayat.')
                    ->visible(fn (MediaEditLog $record): bool => $record->dapatDipulihkan())
                    ->action(function (MediaEditLog $record) {
                        $pengguna = auth()->user();

                        if (! $pengguna instanceof User) {
                            return;
                        }

                        try {
                            EditableImage::restore($record, $pengguna, request());
                        } catch (Throwable $galat) {
                            Notification::make()
                                ->title('Gagal memulihkan')
                                ->body($galat->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Gambar dipulihkan')
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
