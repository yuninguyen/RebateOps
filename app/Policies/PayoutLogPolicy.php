<?php

namespace App\Policies;

use App\Models\PayoutLog;
use App\Models\User;

class PayoutLogPolicy
{
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin() || $user->isFinance()) {
            return true;
        }
        return null;
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PayoutLog $log): bool
    {
        return $log->user_id === $user->id;
    }

    // Staff tạo payout log (gift card redeem / note cho admin xử lý PayPal)
    public function create(User $user): bool
    {
        return true;
    }

    // Staff sửa record của mình (chưa completed)
    public function update(User $user, PayoutLog $log): bool
    {
        return $log->user_id === $user->id;
    }

    public function delete(User $user, PayoutLog $log): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, PayoutLog $log): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, PayoutLog $log): bool
    {
        return $user->isAdmin();
    }
}
