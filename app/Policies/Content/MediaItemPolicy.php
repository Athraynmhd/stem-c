<?php

namespace App\Policies\Content;

use App\Policies\Concerns\AuthorizesCmsModule;
use App\Cms\Access\CmsAccess;

class MediaItemPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::MEDIA;
    }
}
