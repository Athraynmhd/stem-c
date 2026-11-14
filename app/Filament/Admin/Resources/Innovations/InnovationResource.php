<?php

namespace App\Filament\Admin\Resources\Innovations;

use App\Filament\Admin\Resources\Concerns\ScopesEditorOwnership;
use App\Filament\Admin\Resources\Innovations\Pages\CreateInnovation;
use App\Filament\Admin\Resources\Innovations\Pages\EditInnovation;
use App\Filament\Admin\Resources\Innovations\Pages\ListInnovations;
use App\Filament\Admin\Resources\Innovations\Schemas\InnovationForm;
use App\Filament\Admin\Resources\Innovations\Tables\InnovationsTable;
use App\Models\Content\Innovation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class InnovationResource extends Resource
{
    use ScopesEditorOwnership;

    protected static ?string $model = Innovation::class;

    protected static ?string $navigationLabel = 'Produk & HKI';

    protected static ?string $modelLabel = 'Produk / HKI';

    protected static ?string $pluralModelLabel = 'Produk & HKI';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return InnovationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InnovationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInnovations::route('/'),
            'create' => CreateInnovation::route('/create'),
            'edit' => EditInnovation::route('/{record}/edit'),
        ];
    }
}
