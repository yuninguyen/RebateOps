<?php

namespace App\Policies;

use App\Models\RebateTracker;
use App\Models\User;

class RebateTrackerPolicy
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

    public function view(User $user, RebateTracker $tracker): bool
    {
        return $tracker->user_id === $user->id;
    }

    // Staff tạo transaction khi cashback confirmed
    public function create(User $user): bool
    {
        return true;
    }

    // Staff sửa record của mình
    public function update(User $user, RebateTracker $tracker): bool
    {
        return $tracker->user_id === $user->id;
    }

    public function delete(User $user, RebateTracker $tracker): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, RebateTracker $tracker): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, RebateTracker $tracker): bool
    {
        return $user->isAdmin();
    }
}
