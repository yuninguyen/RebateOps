<?php

namespace App\Policies;

use App\Models\User;

class ActivityPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(User $user): bool
    {
        // Chỉ Admin mới được xem danh sách nhật ký
        return $user->isAdmin();
    }
}
