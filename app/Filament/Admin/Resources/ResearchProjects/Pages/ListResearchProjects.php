<?php

namespace App\Filament\Admin\Resources\ResearchProjects\Pages;

use App\Filament\Admin\Resources\ResearchProjects\ResearchProjectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResearchProjects extends ListRecords
{
    protected static string $resource = ResearchProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
