<?php

namespace App\Policies\Cms;

use App\Models\Auth\User;
use App\Models\Cms\MediaEditLog;

/**
 * Log audit hanya boleh dibaca. Semua jalur tulis ditutup di sini, bukan cuma
 * di resource Filament, karena panel mengizinkan aksi ketika method policy-nya
 * tidak ada.
 */
class MediaEditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, MediaEditLog $record): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, MediaEditLog $record): bool
    {
        return false;
    }

    public function delete(User $user, MediaEditLog $record): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, MediaEditLog $record): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, MediaEditLog $record): bool
    {
        return false;
    }

    public function reorder(User $user): bool
    {
        return false;
    }
}
