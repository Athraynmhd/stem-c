<?php

namespace App\Filament\Admin\Resources\ResearchProjects\Pages;

use App\Filament\Admin\Resources\Concerns\AssignsOwnedWorkflow;
use App\Filament\Admin\Resources\ResearchProjects\ResearchProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchProject extends EditRecord
{
    use AssignsOwnedWorkflow;

    protected static string $resource = ResearchProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
