<?php

namespace App\Filament\Admin\Resources\News\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Konten Berita')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true),
                        TextInput::make('category')
                            ->label('Kategori')
                            ->placeholder('Kegiatan / Riset / Kolaborasi'),
                        TagsInput::make('tags')
                            ->label('Tag')
                            ->placeholder('Tambah tag lalu Enter')
                            ->helperText('Untuk tapis di halaman berita publik. Contoh: IMAGy, MoU, webinar.')
                            ->columnSpanFull(),
                        Textarea::make('excerpt')
                            ->label('Ringkasan')
                            ->rows(3)
                            ->columnSpanFull(),
                        ContentFormFields::richBody('body', 'Isi', 'news/body'),
                        FileUpload::make('cover_image')
                            ->label('Gambar sampul')
                            ->image()
                            ->directory('news')
                            ->helperText('Opsional. Tampil di banner, terpisah dari gambar di dalam isi berita.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Publikasi')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'review' => 'Ditinjau',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft'),
                        DateTimePicker::make('published_at')
                            ->label('Tanggal tayang'),
                    ])
                    ->columns(2),
                Section::make('English (opsional)')
                    ->collapsed()
                    ->schema([
                        TextInput::make('title_en')->label('Title'),
                        Textarea::make('excerpt_en')->label('Excerpt')->rows(3)->columnSpanFull(),
                        ContentFormFields::richBody('body_en', 'Content', 'news/body'),
                    ])
                    ->columns(2),
                ContentFormFields::seo(),
            ]);
    }
}
