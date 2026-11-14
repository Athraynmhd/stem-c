<?php

namespace App\Filament\Admin\Resources\ContactMessages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ContactMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->label('Nama')->disabled(),
                TextInput::make('email')->label('Email')->disabled(),
                TextInput::make('subject')->label('Subjek')->disabled(),
                Textarea::make('message')->label('Pesan')->rows(6)->disabled()->columnSpanFull(),
                Select::make('status')
                    ->options([
                        'new' => 'Baru',
                        'read' => 'Dibaca',
                        'archived' => 'Arsip',
                    ])
                    ->required(),
            ]);
    }
}
