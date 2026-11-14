<?php

namespace App\Filament\Admin\Resources\ResearchProjects\Pages;

use App\Filament\Admin\Resources\Concerns\AssignsOwnedWorkflow;
use App\Filament\Admin\Resources\ResearchProjects\ResearchProjectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateResearchProject extends CreateRecord
{
    use AssignsOwnedWorkflow;

    protected static string $resource = ResearchProjectResource::class;
}
