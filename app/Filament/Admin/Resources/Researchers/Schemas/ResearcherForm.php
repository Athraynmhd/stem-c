<?php

namespace App\Filament\Admin\Resources\Researchers\Schemas;

use App\Cms\Access\CmsAccess;
use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\Researcher;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ResearcherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profil Peneliti')->schema([
                TextInput::make('name')
                    ->label('Nama lengkap')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))
                    ->helperText('Tulis gelar depan dan belakang di sini, misalnya: Dr. Siti Rahmawati, M.Pd. atau Muhammad Fauzan, M.Eng.'),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Boleh disunting agar URL lebih pendek (tanpa gelar).'),
                FileUpload::make('photo')->label('Foto')->image()->directory('researchers'),
                TextInput::make('expertise')->label('Keahlian')->columnSpanFull(),
                TextInput::make('affiliation')->label('Afiliasi'),
                Select::make('research_group_id')->label('Kelompok Riset')->relationship('group', 'name')->preload()->searchable(),
                Select::make('research_group')->label('Kode kelompok (cadangan)')->options(Researcher::GROUPS)->helperText('Dipakai bila relasi kelompok belum diisi.'),
                Textarea::make('education')->label('Pendidikan')->rows(3),
                Textarea::make('interests')->label('Minat Riset')->rows(3),
                Textarea::make('bio')->label('Biografi')->rows(5)->columnSpanFull(),
            ])->columns(2),
            Section::make('Kontak & Profil Ilmiah')->schema([
                TextInput::make('email')->email()->label('Email'),
                TextInput::make('google_scholar')->url()->label('Google Scholar'),
                TextInput::make('scopus')->url()->label('Scopus'),
                TextInput::make('sinta')->url()->label('SINTA'),
                Select::make('user_id')
                    ->label('Akun CMS')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->visible(fn () => CmsAccess::canPublish(auth()->user())),
            ])->columns(2),
            Section::make('Status')->schema([
                TextInput::make('sort_order')->numeric()->label('Urutan')->default(0),
                Toggle::make('is_published')->label('Tampilkan di situs')->default(true)
                    ->disabled(fn () => ! CmsAccess::canPublish(auth()->user())),
            ])->columns(2),
            Section::make('English (opsional)')->collapsed()->schema([
                TextInput::make('name_en')->label('Name')->helperText('Nama lengkap dengan gelar, versi Inggris bila berbeda.'),
                TextInput::make('expertise_en')->label('Expertise')->columnSpanFull(),
                Textarea::make('bio_en')->label('Biography')->rows(4)->columnSpanFull(),
            ]),
            ContentFormFields::seo(),
        ]);
    }
}
