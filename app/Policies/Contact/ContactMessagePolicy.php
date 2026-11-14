<?php

namespace App\Policies\Contact;

use App\Policies\Concerns\AuthorizesCmsModule;
use App\Models\Auth\User;
use App\Cms\Access\CmsAccess;

class ContactMessagePolicy
{
    use AuthorizesCmsModule;

    protected function module(): string
    {
        return CmsAccess::CONTACT;
    }

    /**
     * Pesan hanya masuk lewat form publik. Admin tidak membuat entri palsu.
     */
    public function create(User $user): bool
    {
        return false;
    }
}
