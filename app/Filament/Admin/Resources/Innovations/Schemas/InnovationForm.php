<?php

namespace App\Filament\Admin\Resources\Innovations\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\Innovation;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class InnovationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Produk dan Kekayaan Intelektual')->schema([
                TextInput::make('title')->label('Judul')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))->columnSpanFull(),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Select::make('type')->label('Jenis')->options(Innovation::TYPES)->required()->default('produk'),
                Textarea::make('description')->label('Deskripsi')->rows(5)->columnSpanFull(),
                Textarea::make('specifications')->label('Spesifikasi')->rows(4)->columnSpanFull(),
                FileUpload::make('image')->label('Gambar')->image()->directory('innovations')->columnSpanFull(),
                TextInput::make('registration_number')->label('Nomor Pendaftaran'),
                TextInput::make('year')->label('Tahun')->numeric(),
                TextInput::make('holders')->label('Pemegang / Inventor'),
                TextInput::make('hki_status')->label('Status HKI'),
            ])->columns(2),
            ContentFormFields::workflow(),
            Section::make('English (opsional)')->collapsed()->schema([
                TextInput::make('title_en')->label('Title')->columnSpanFull(),
                Textarea::make('description_en')->label('Description')->rows(4)->columnSpanFull(),
            ]),
            ContentFormFields::seo(),
        ]);
    }
}
