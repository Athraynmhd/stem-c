<?php

namespace App\Policies\Content;

use App\Policies\Concerns\AuthorizesCmsModule;
use App\Cms\Access\CmsAccess;

class NewsPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::NEWS;
    }
}
