<?php

namespace App\Filament\Admin\Resources\ResearchGroups\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ResearchGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Kelompok Riset')->schema([
                TextInput::make('name')->label('Nama')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state))),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                TextInput::make('code')->label('Kode')->helperText('rnd, manufacturing, pedagogical, marketing'),
                Textarea::make('focus')->label('Fokus kajian')->rows(4)->columnSpanFull(),
                FileUpload::make('image')->label('Gambar')->image()->directory('research-groups')->columnSpanFull(),
                TextInput::make('sort_order')->numeric()->label('Urutan')->default(0),
                Toggle::make('is_published')->label('Tampilkan di situs')->default(true),
            ])->columns(2),
            Section::make('English (opsional)')->collapsed()->schema([
                TextInput::make('name_en')->label('Name'),
                Textarea::make('focus_en')->label('Focus')->rows(3)->columnSpanFull(),
            ]),
            ContentFormFields::seo(),
        ]);
    }
}
