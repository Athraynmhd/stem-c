<?php

namespace App\Policies\Cms;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use App\Models\Cms\ContentActivityLog;

class ContentActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return CmsAccess::can($user, CmsAccess::AUDIT);
    }

    public function view(User $user, ContentActivityLog $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, ContentActivityLog $record): bool
    {
        return false;
    }

    public function delete(User $user, ContentActivityLog $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, ContentActivityLog $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, ContentActivityLog $record): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
