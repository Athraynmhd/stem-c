<?php

namespace App\Filament\Admin\Resources\Innovations\Pages;

use App\Filament\Admin\Resources\Concerns\AssignsOwnedWorkflow;
use App\Filament\Admin\Resources\Innovations\InnovationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInnovation extends EditRecord
{
    use AssignsOwnedWorkflow;

    protected static string $resource = InnovationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
