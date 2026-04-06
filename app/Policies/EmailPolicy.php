<?php

namespace App\Policies;

use App\Models\Email;
use App\Models\User;

class EmailPolicy
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

    public function view(User $user, Email $email): bool
    {
        // Admin & Finance được xem toàn bộ hòm thư
        if ($user->isAdmin() || $user->isFinance()) {
            return true;
        }

        // Staff chỉ xem email liên kết với account của mình
        return $email->accounts()->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Email $email): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Email $email): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Email $email): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Email $email): bool
    {
        return $user->isAdmin();
    }
}
