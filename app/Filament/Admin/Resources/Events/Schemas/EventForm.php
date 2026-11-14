<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\Event;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Kegiatan')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        Select::make('category')
                            ->label('Kategori')
                            ->options(Event::CATEGORIES),
                        TextInput::make('location')
                            ->label('Lokasi')
                            ->placeholder('Aula Lab Terpadu USK'),
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->columnSpanFull(),
                        ContentFormFields::richBody('body', 'Deskripsi', 'events/body'),
                        FileUpload::make('cover_image')
                            ->label('Gambar sampul')
                            ->image()
                            ->directory('events')
                            ->helperText('Opsional. Tampil di banner halaman, terpisah dari gambar di dalam deskripsi.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Jadwal & Publikasi')
                    ->schema([
                        DateTimePicker::make('starts_at')
                            ->label('Mulai')
                            ->required(),
                        DateTimePicker::make('ends_at')
                            ->label('Selesai')
                            ->after('starts_at'),
                        TextInput::make('registration_url')
                            ->label('Tautan pendaftaran')
                            ->url()
                            ->placeholder('https://'),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'Ditinjau',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),
                Section::make('English (opsional)')
                    ->collapsed()
                    ->schema([
                        TextInput::make('title_en')->label('Title'),
                        Textarea::make('excerpt_en')->label('Excerpt')->rows(3)->columnSpanFull(),
                        ContentFormFields::richBody('body_en', 'Content', 'events/body'),
                    ]),
                ContentFormFields::seo(),
            ]);
    }
}
