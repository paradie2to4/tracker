<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

/**
 * Supply-chain master data: everyone can read it, only administrators
 * can create or change it.
 */
class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Organization $organization): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }
}
