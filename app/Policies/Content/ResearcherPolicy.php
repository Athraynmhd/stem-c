<?php

namespace App\Policies\Content;

use App\Cms\Access\CmsAccess;
use App\Models\Auth\User;
use App\Models\Content\Researcher;
use App\Policies\Concerns\AuthorizesCmsModule;
use Illuminate\Database\Eloquent\Model;

class ResearcherPolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::RESEARCHERS;
    }

    public function view(User $user, Model $record): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        if ($user->hasRole('editor')) {
            return $record instanceof Researcher && (int) $record->user_id === (int) $user->getKey();
        }

        return true;
    }

    public function create(User $user): bool
    {
        return CmsAccess::canPublish($user) && $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->view($user, $record);
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
