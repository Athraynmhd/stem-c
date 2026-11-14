<?php

namespace App\Policies\Content;

use App\Cms\Access\CmsAccess;
use App\Policies\Concerns\AuthorizesCmsModule;

class PartnershipPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::RESEARCH;
    }
}
