<?php

namespace App\Policies\Content;

use App\Policies\Concerns\AuthorizesCmsModule;
use App\Cms\Access\CmsAccess;

class PagePolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::PAGES;
    }
}
