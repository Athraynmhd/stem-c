<?php

namespace App\Policies\Content;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use App\Policies\Concerns\AuthorizesCmsModule;
use Illuminate\Database\Eloquent\Model;

class ResearchGroupPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::RESEARCH_GROUPS;
    }

    public function create(User $user): bool
    {
        return CmsAccess::canPublish($user) && $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        return CmsAccess::canPublish($user) && $this->viewAny($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return CmsAccess::canPublish($user);
    }

    public function deleteAny(User $user): bool
    {
        return CmsAccess::canPublish($user);
    }
}
