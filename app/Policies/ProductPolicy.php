<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

/**
 * Every authenticated user can read and maintain products. Only
 * administrators can activate or deactivate them.
 *
 * Methods that return true today are still worth having: they are the
 * single place where organisation-level access rules will be added later.
 */
class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Product $product): bool
    {
        return true;
    }

    public function changeStatus(User $user, Product $product): bool
    {
        return $user->isAdmin();
    }
}
