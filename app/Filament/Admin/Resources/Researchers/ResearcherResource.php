<?php

namespace App\Filament\Admin\Resources\Researchers;

use App\Filament\Admin\Resources\Researchers\Pages\CreateResearcher;
use App\Filament\Admin\Resources\Researchers\Pages\EditResearcher;
use App\Filament\Admin\Resources\Researchers\Pages\ListResearchers;
use App\Filament\Admin\Resources\Researchers\Schemas\ResearcherForm;
use App\Filament\Admin\Resources\Researchers\Tables\ResearchersTable;
use App\Models\Auth\User;
use App\Models\Content\Researcher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResearcherResource extends Resource
{
    protected static ?string $model = Researcher::class;

    protected static ?string $navigationLabel = 'Peneliti';

    protected static ?string $modelLabel = 'Peneliti';

    protected static ?string $pluralModelLabel = 'Peneliti';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ResearcherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchersTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
        $user = auth()->user();

        if ($user instanceof User && $user->hasRole('editor')) {
            $query->where('user_id', $user->getKey());
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchers::route('/'),
            'create' => CreateResearcher::route('/create'),
            'edit' => EditResearcher::route('/{record}/edit'),
        ];
    }
}
