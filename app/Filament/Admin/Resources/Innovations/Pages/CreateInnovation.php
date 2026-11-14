<?php

namespace App\Filament\Admin\Resources\Innovations\Pages;

use App\Filament\Admin\Resources\Concerns\AssignsOwnedWorkflow;
use App\Filament\Admin\Resources\Innovations\InnovationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInnovation extends CreateRecord
{
    use AssignsOwnedWorkflow;

    protected static string $resource = InnovationResource::class;
}
