<?php

namespace App\Filament\Admin\Resources\Partnerships\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\Partnership;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PartnershipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Kemitraan')->schema([
                TextInput::make('title')->label('Judul')->required()->columnSpanFull(),
                TextInput::make('partner_name')->label('Nama Mitra')->required(),
                Select::make('type')->label('Jenis')->options(Partnership::TYPES)->required()->default('mou'),
                Textarea::make('scope')->label('Ruang Lingkup')->rows(4)->columnSpanFull(),
                DatePicker::make('started_on')->label('Tanggal Mulai'),
                DatePicker::make('ended_on')->label('Tanggal Berakhir')->afterOrEqual('started_on'),
                Toggle::make('is_active')->label('Aktif')->default(true),
                Toggle::make('is_published')->label('Tampilkan di situs')->default(true),
            ])->columns(2),
            ContentFormFields::seo(),
        ]);
    }
}
