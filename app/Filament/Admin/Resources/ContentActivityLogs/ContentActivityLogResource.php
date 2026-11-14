<?php

namespace App\Filament\Admin\Resources\ContentActivityLogs;

use App\Cms\Access\CmsAccess;
use App\Filament\Admin\Resources\ContentActivityLogs\Pages\ListContentActivityLogs;
use App\Filament\Admin\Resources\ContentActivityLogs\Tables\ContentActivityLogsTable;
use App\Models\Auth\User;
use App\Models\Cms\ContentActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentActivityLogResource extends Resource
{
    protected static ?string $model = ContentActivityLog::class;

    protected static ?string $navigationLabel = 'Riwayat Konten';

    protected static ?string $modelLabel = 'Riwayat Konten';

    protected static ?string $pluralModelLabel = 'Riwayat Konten';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 97;

    public static function table(Table $table): Table
    {
        return ContentActivityLogsTable::configure($table);
    }

    public static function canAccess(): bool
    {
        return CmsAccess::can(auth()->user() instanceof User ? auth()->user() : null, CmsAccess::AUDIT);
    }

    public static function getPages(): array
    {
        return ['index' => ListContentActivityLogs::route('/')];
    }
}
