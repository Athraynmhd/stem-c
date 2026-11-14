<?php

namespace App\Filament\Admin\Resources\Pages\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('section')
                            ->label('Bagian')
                            ->options([
                                'sejarah' => 'Sejarah',
                                'visi-misi' => 'Visi & Misi',
                                'struktur' => 'Struktur Organisasi',
                                'lainnya' => 'Lainnya',
                            ]),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0),
                        Toggle::make('is_published')
                            ->label('Tayang')
                            ->default(true),
                        ContentFormFields::richBody('content', 'Konten', 'pages/body'),
                        FileUpload::make('hero_image')
                            ->label('Gambar hero')
                            ->image()
                            ->directory('pages')
                            ->helperText('Opsional. Banner halaman, terpisah dari gambar di dalam konten.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('English (opsional)')
                    ->collapsed()
                    ->schema([
                        TextInput::make('title_en')->label('Title'),
                        TextInput::make('section_en')->label('Section'),
                        ContentFormFields::richBody('content_en', 'Content', 'pages/body'),
                    ])
                    ->columns(2),
                ContentFormFields::seo(),
            ]);
    }
}
