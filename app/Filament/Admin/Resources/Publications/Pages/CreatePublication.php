<?php

namespace App\Filament\Admin\Resources\Publications\Pages;

use App\Filament\Admin\Resources\Concerns\AssignsOwnedWorkflow;
use App\Filament\Admin\Resources\Publications\PublicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePublication extends CreateRecord
{
    use AssignsOwnedWorkflow;

    protected static string $resource = PublicationResource::class;
}
