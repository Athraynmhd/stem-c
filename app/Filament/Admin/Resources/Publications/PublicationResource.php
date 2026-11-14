<?php

namespace App\Filament\Admin\Resources\Publications;

use App\Filament\Admin\Resources\Concerns\ScopesEditorOwnership;
use App\Filament\Admin\Resources\Publications\Pages\CreatePublication;
use App\Filament\Admin\Resources\Publications\Pages\EditPublication;
use App\Filament\Admin\Resources\Publications\Pages\ListPublications;
use App\Filament\Admin\Resources\Publications\Schemas\PublicationForm;
use App\Filament\Admin\Resources\Publications\Tables\PublicationsTable;
use App\Models\Content\Publication;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PublicationResource extends Resource
{
    use ScopesEditorOwnership;

    protected static ?string $model = Publication::class;

    protected static ?string $navigationLabel = 'Publikasi';

    protected static ?string $modelLabel = 'Publikasi';

    protected static ?string $pluralModelLabel = 'Publikasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PublicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PublicationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPublications::route('/'),
            'create' => CreatePublication::route('/create'),
            'edit' => EditPublication::route('/{record}/edit'),
        ];
    }
}
