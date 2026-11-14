<?php

namespace App\Policies\Concerns;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;

trait AuthorizesOwnedWorkflow
{
    abstract protected function module(): string;

    public function viewAny(User $user): bool
    {
        return CmsAccess::can($user, $this->module());
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user) && CmsAccess::owns($user, $record);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        if (! $this->viewAny($user) || ! CmsAccess::owns($user, $record)) {
            return false;
        }

        if ($user->hasRole('editor') && $record->getAttribute('workflow_status') === CmsAccess::WORKFLOW_PUBLISHED) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Model $record): bool
    {
        return CmsAccess::canPublish($user) && $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return CmsAccess::canPublish($user) && $this->viewAny($user);
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->delete($user, $record);
    }

    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $user->hasRole('super_admin');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function replicate(User $user, Model $record): bool
    {
        return $this->create($user);
    }

    public function reorder(User $user): bool
    {
        return CmsAccess::canPublish($user) && $this->viewAny($user);
    }

    public function publish(User $user, ?Model $record = null): bool
    {
        return CmsAccess::canPublish($user);
    }
}
