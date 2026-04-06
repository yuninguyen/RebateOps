<?php

namespace App\Policies;

use App\Models\UserPayment;
use App\Models\User;

class UserPaymentPolicy
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

    // Staff chỉ xem phiếu lương của mình
    public function view(User $user, UserPayment $payment): bool
    {
        return $payment->user_id === $user->id;
    }

    // Chỉ Admin tạo phiếu lương
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, UserPayment $payment): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, UserPayment $payment): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, UserPayment $payment): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, UserPayment $payment): bool
    {
        return $user->isAdmin();
    }
}
