<?php

namespace App\Policies\Content;

use App\Cms\Access\CmsAccess;
use App\Policies\Concerns\AuthorizesOwnedWorkflow;

class ResearchProjectPolicy
{
    use AuthorizesOwnedWorkflow;

    protected function module(): string
    {
        return CmsAccess::RESEARCH;
    }
}
