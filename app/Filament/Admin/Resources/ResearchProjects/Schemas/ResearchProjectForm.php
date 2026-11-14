<?php

namespace App\Filament\Admin\Resources\ResearchProjects\Schemas;

use App\Filament\Admin\Support\ContentFormFields;
use App\Models\Content\ResearchProject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ResearchProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Data Penelitian')->schema([
                TextInput::make('title')->label('Judul')->required()->live(onBlur: true)->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug((string) $state)))->columnSpanFull(),
                TextInput::make('slug')->required()->unique(ignoreRecord: true),
                Select::make('research_group_id')->label('Kelompok Riset')->relationship('group', 'name')->preload()->searchable(),
                TextInput::make('year')->label('Tahun')->numeric()->required()->default((int) date('Y')),
                TextInput::make('leader')->label('Ketua'),
                TextInput::make('members')->label('Anggota'),
                Select::make('researchers')->label('Peneliti terkait')->relationship('researchers', 'name')->multiple()->preload()->searchable()->columnSpanFull(),
                TextInput::make('funding')->label('Pendanaan'),
                Select::make('status')->label('Status pelaksanaan')->options(ResearchProject::STATUSES)->required()->default('berjalan'),
                Textarea::make('abstract')->label('Abstrak')->rows(5)->columnSpanFull(),
            ])->columns(2),
            ContentFormFields::workflow(),
            Section::make('English (opsional)')->collapsed()->schema([
                TextInput::make('title_en')->label('Title')->columnSpanFull(),
                Textarea::make('abstract_en')->label('Abstract')->rows(4)->columnSpanFull(),
            ]),
            ContentFormFields::seo(),
        ]);
    }
}
