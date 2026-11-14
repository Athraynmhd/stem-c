<?php

namespace App\Filament\Admin\Resources\MediaItems\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MediaItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Media')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('type')
                            ->label('Jenis')
                            ->options([
                                'photo' => 'Foto',
                                'video' => 'Video',
                            ])
                            ->default('photo')
                            ->live()
                            ->required(),
                        TextInput::make('album')
                            ->label('Album')
                            ->placeholder('Seminar Nasional 2026')
                            ->datalist(fn () => \App\Models\Content\MediaItem::query()
                                ->whereNotNull('album')
                                ->distinct()
                                ->pluck('album')
                                ->all()),
                        FileUpload::make('image')
                            ->label(fn (callable $get): string => $get('type') === 'video' ? 'Thumbnail video' : 'Foto')
                            ->image()
                            ->directory('gallery')
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('video_url')
                            ->label('URL video')
                            ->url()
                            ->placeholder('https://www.youtube.com/watch?v=...')
                            ->helperText('Wajib diisi bila jenisnya video.')
                            ->required(fn (callable $get): bool => $get('type') === 'video')
                            ->visible(fn (callable $get): bool => $get('type') === 'video')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Penataan')
                    ->schema([
                        DatePicker::make('captured_at')
                            ->label('Tanggal pengambilan'),
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu.'),
                        Toggle::make('is_published')
                            ->label('Tampilkan di situs')
                            ->default(true),
                    ])
                    ->columns(3),
            ]);
    }
}
