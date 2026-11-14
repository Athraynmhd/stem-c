<?php

namespace App\Policies\Chrome;

use App\Policies\Concerns\AuthorizesCmsModule;
use App\Cms\Access\CmsAccess;

class SiteSectionPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::HOMEPAGE;
    }
}
