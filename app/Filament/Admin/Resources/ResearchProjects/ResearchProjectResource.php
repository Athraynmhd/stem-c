<?php

namespace App\Filament\Admin\Resources\ResearchProjects;

use App\Filament\Admin\Resources\Concerns\ScopesEditorOwnership;
use App\Filament\Admin\Resources\ResearchProjects\Pages\CreateResearchProject;
use App\Filament\Admin\Resources\ResearchProjects\Pages\EditResearchProject;
use App\Filament\Admin\Resources\ResearchProjects\Pages\ListResearchProjects;
use App\Filament\Admin\Resources\ResearchProjects\Schemas\ResearchProjectForm;
use App\Filament\Admin\Resources\ResearchProjects\Tables\ResearchProjectsTable;
use App\Models\Content\ResearchProject;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ResearchProjectResource extends Resource
{
    use ScopesEditorOwnership;

    protected static ?string $model = ResearchProject::class;

    protected static ?string $navigationLabel = 'Katalog Penelitian';

    protected static ?string $modelLabel = 'Penelitian';

    protected static ?string $pluralModelLabel = 'Katalog Penelitian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return ResearchProjectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ResearchProjectsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListResearchProjects::route('/'),
            'create' => CreateResearchProject::route('/create'),
            'edit' => EditResearchProject::route('/{record}/edit'),
        ];
    }
}
