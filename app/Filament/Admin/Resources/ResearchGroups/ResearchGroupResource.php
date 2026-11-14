<?php

namespace App\Filament\Admin\Resources\ResearchGroups;

use App\Filament\Admin\Resources\ResearchGroups\Pages\CreateResearchGroup;
use App\Filament\Admin\Resources\ResearchGroups\Pages\EditResearchGroup;
use App\Filament\Admin\Resources\ResearchGroups\Pages\ListResearchGroups;
use App\Filament\Admin\Resources\ResearchGroups\Schemas\ResearchGroupForm;
use App\Filament\Admin\Resources\ResearchGroups\Tables\ResearchGroupsTable;
use App\Models\Content\ResearchGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ResearchGroupResource extends Resource
{
    protected static ?string $model = ResearchGroup::class;

    protected static ?string $navigationLabel = 'Kelompok Riset';

    protected static ?string $modelLabel = 'Kelompok Riset';

    protected static ?string $pluralModelLabel = 'Kelompok Riset';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return ResearchGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchGroupsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchGroups::route('/'),
            'create' => CreateResearchGroup::route('/create'),
            'edit' => EditResearchGroup::route('/{record}/edit'),
        ];
    }
}
