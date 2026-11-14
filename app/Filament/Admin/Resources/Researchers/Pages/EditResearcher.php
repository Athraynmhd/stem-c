<?php

namespace App\Filament\Admin\Resources\Researchers\Pages;

use App\Filament\Admin\Resources\Researchers\ResearcherResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditResearcher extends EditRecord
{
    protected static string $resource = ResearcherResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make(), RestoreAction::make(), ForceDeleteAction::make()];
    }
}
