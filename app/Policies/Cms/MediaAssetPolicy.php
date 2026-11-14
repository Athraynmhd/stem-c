<?php

namespace App\Policies\Cms;

use App\Cms\Access\CmsAccess;
use App\Policies\Concerns\AuthorizesCmsModule;

class MediaAssetPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::MEDIA_LIBRARY;
    }
}
