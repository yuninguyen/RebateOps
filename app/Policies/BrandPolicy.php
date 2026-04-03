<?php

namespace App\Policies;

use App\Models\Brand;
use App\Models\User;

class BrandPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Brand $brand): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Brand $brand): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Brand $brand): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Brand $brand): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Brand $brand): bool
    {
        return $user->isAdmin();
    }
}
