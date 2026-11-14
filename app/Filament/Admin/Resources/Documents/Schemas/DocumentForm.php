<?php

namespace App\Filament\Admin\Resources\Documents\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\Document;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dokumen')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul')
                            ->required()
                            ->columnSpanFull(),
                        Select::make('category')
                            ->label('Kategori')
                            ->options(Document::CATEGORIES)
                            ->default('panduan')
                            ->required(),
                        DatePicker::make('published_on')
                            ->label('Tanggal terbit'),
                        Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Berkas')
                    ->description('Isi salah satu: unggah berkas ke server, atau tautkan ke repositori luar.')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Unggah berkas')
                            ->directory('documents')
                            ->acceptedFileTypes([
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/zip',
                            ])
                            ->maxSize(20480)
                            ->helperText('PDF, Word, Excel, atau ZIP. Maksimal 20 MB.')
                            // Ukuran dicatat saat unggah supaya halaman publik
                            // tidak perlu menanyakan disk untuk tiap baris.
                            ->saveUploadedFileUsing(function (UploadedFile $file, callable $set): string {
                                $set('file_size', $file->getSize());

                                return $file->store('documents', 'public');
                            })
                            ->columnSpanFull(),
                        TextInput::make('external_url')
                            ->label('Atau tautan luar')
                            ->url()
                            ->placeholder('https://drive.google.com/...')
                            ->requiredWithout('file')
                            ->columnSpanFull(),
                    ]),
                Section::make('Penataan')
                    ->schema([
                        TextInput::make('sort_order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(0)
                            ->helperText('Angka lebih kecil tampil lebih dulu.'),
                        Toggle::make('is_published')
                            ->label('Tampilkan di situs')
                            ->default(true),
                    ])
                    ->columns(2),
                ContentFormFields::seo(),
            ]);
    }
}
