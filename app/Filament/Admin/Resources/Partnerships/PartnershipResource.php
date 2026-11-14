<?php

namespace App\Filament\Admin\Resources\Partnerships;

use App\Filament\Admin\Resources\Partnerships\Pages\CreatePartnership;
use App\Filament\Admin\Resources\Partnerships\Pages\EditPartnership;
use App\Filament\Admin\Resources\Partnerships\Pages\ListPartnerships;
use App\Filament\Admin\Resources\Partnerships\Schemas\PartnershipForm;
use App\Filament\Admin\Resources\Partnerships\Tables\PartnershipsTable;
use App\Models\Content\Partnership;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PartnershipResource extends Resource
{
    protected static ?string $model = Partnership::class;

    protected static ?string $navigationLabel = 'MoU / MoA';

    protected static ?string $modelLabel = 'Kemitraan';

    protected static ?string $pluralModelLabel = 'MoU / MoA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|\UnitEnum|null $navigationGroup = 'Konten';

    protected static ?int $navigationSort = 7;

    public static function form(Schema $schema): Schema
    {
        return PartnershipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PartnershipsTable::configure($table);
    }

    public static function getPages(): array
    {
        return ['index' => ListPartnerships::route('/'), 'create' => CreatePartnership::route('/create'), 'edit' => EditPartnership::route('/{record}/edit')];
    }
}
