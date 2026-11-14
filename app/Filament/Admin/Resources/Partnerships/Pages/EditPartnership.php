<?php

namespace App\Filament\Admin\Resources\Partnerships\Pages;

use App\Filament\Admin\Resources\Partnerships\PartnershipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnership extends EditRecord
{
    protected static string $resource = PartnershipResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
