<?php

namespace App\Filament\Admin\Resources\LoginActivityLogs;

use App\Filament\Admin\Resources\LoginActivityLogs\Pages\ListLoginActivityLogs;
use App\Filament\Admin\Resources\LoginActivityLogs\Tables\LoginActivityLogsTable;
use App\Models\Auth\User;
use App\Models\Cms\LoginActivityLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LoginActivityLogResource extends Resource
{
    protected static ?string $model = LoginActivityLog::class;

    protected static ?string $navigationLabel = 'Riwayat Login';

    protected static ?string $modelLabel = 'Riwayat Login';

    protected static ?string $pluralModelLabel = 'Riwayat Login';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|\UnitEnum|null $navigationGroup = 'Sistem';

    protected static ?int $navigationSort = 96;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->hasRole('super_admin');
    }

    public static function table(Table $table): Table
    {
        return LoginActivityLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginActivityLogs::route('/'),
        ];
    }
}
