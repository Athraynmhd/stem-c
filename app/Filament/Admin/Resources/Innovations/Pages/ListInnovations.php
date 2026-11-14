<?php

namespace App\Filament\Admin\Resources\Innovations\Pages;

use App\Filament\Admin\Resources\Innovations\InnovationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInnovations extends ListRecords
{
    protected static string $resource = InnovationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
