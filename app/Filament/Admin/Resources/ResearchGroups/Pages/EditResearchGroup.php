<?php

namespace App\Filament\Admin\Resources\ResearchGroups\Pages;

use App\Filament\Admin\Resources\ResearchGroups\ResearchGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchGroup extends EditRecord
{
    protected static string $resource = ResearchGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
