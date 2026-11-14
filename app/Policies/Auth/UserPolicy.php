<?php

namespace App\Policies\Auth;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return CmsAccess::canManageUsers($user);
    }

    public function view(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, User $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, User $record): bool
    {
        return $this->viewAny($user) && $user->isNot($record);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, User $record): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $record): bool
    {
        return false;
    }
}
