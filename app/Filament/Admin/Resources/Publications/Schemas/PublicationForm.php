<?php

namespace App\Filament\Admin\Resources\Publications\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\Publication;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PublicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identitas Publikasi')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('authors')
                            ->label('Penulis')
                            ->placeholder('Nama penulis dipisahkan koma')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('researchers')
                            ->label('Peneliti terkait')
                            ->relationship('researchers', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Jenis')
                            ->options(Publication::TYPES)
                            ->default('jurnal')
                            ->required(),
                        TextInput::make('year')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(1990)
                            ->maxValue((int) date('Y') + 1)
                            ->default((int) date('Y'))
                            ->required(),
                        TextInput::make('venue')
                            ->label('Jurnal / Konferensi / Penerbit')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Tautan & Berkas')
                    ->description('DOI dipakai lebih dulu sebagai tautan publik, lalu URL, lalu berkas PDF.')
                    ->schema([
                        TextInput::make('doi')
                            ->label('DOI')
                            ->placeholder('10.1234/abcd'),
                        TextInput::make('url')
                            ->label('URL')
                            ->url()
                            ->placeholder('https://'),
                        Textarea::make('abstract')
                            ->label('Abstrak')
                            ->rows(4)
                            ->columnSpanFull(),
                        FileUpload::make('file')
                            ->label('Berkas PDF')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('publications')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                ContentFormFields::workflow(),
                Section::make('English (opsional)')
                    ->collapsed()
                    ->schema([
                        TextInput::make('title_en')->label('Title')->columnSpanFull(),
                        Textarea::make('abstract_en')->label('Abstract')->rows(4)->columnSpanFull(),
                    ]),
                ContentFormFields::seo(),
            ]);
    }
}
