<?php

namespace App\Policies\Cms;

use App\Models\Auth\User;
use App\Models\Cms\LoginActivityLog;

class LoginActivityLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, LoginActivityLog $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, LoginActivityLog $record): bool
    {
        return false;
    }

    public function delete(User $user, LoginActivityLog $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
