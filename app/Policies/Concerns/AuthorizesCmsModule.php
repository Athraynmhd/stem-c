<?php

namespace App\Policies\Concerns;

use App\Models\Auth\User;
use App\Cms\Access\CmsAccess;
use Illuminate\Database\Eloquent\Model;

/**
 * Filament mengizinkan aksi bila method policy tidak ada. Semua method CRUD
 * yang dipanggil panel harus didefinisikan supaya editor tidak lolos lewat
 * celah deleteAny / reorder.
 */
trait AuthorizesCmsModule
{
    abstract protected function module(): string;

    public function viewAny(User $user): bool
    {
        return CmsAccess::can($user, $this->module());
    }

    public function view(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function update(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function restore(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->viewAny($user);
    }

    public function replicate(User $user, Model $record): bool
    {
        return $this->viewAny($user);
    }

    public function reorder(User $user): bool
    {
        return $this->viewAny($user);
    }
}
