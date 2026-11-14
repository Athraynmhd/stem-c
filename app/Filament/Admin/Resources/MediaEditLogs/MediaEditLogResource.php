<?php

namespace App\Filament\Admin\Resources\MediaEditLogs;

use App\Filament\Admin\Resources\MediaEditLogs\Pages\ListMediaEditLogs;
use App\Filament\Admin\Resources\MediaEditLogs\Tables\MediaEditLogsTable;
use App\Models\Cms\MediaEditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * Log audit penggantian gambar dari halaman publik.
 *
 * Hanya bisa dibaca: tidak ada create, edit, maupun hapus, karena catatan yang
 * bisa disunting kehilangan gunanya sebagai bukti. Satu-satunya aksi adalah
 * memulihkan gambar ke versi sebelum penggantian.
 */
class MediaEditLogResource extends Resource
{
    protected static ?string $model = MediaEditLog::class;

    protected static ?string $navigationLabel = 'Riwayat Ganti Gambar';

    protected static ?string $modelLabel = 'Riwayat Ganti Gambar';

    protected static ?string $pluralModelLabel = 'Riwayat Ganti Gambar';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 98;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && $user->hasRole('super_admin');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return MediaEditLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMediaEditLogs::route('/'),
        ];
    }
}
