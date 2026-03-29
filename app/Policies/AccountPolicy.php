<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class AccountPolicy
{
    /**
     * Helper function để kiểm tra có phải Admin không.
     * Thay 'yuninguyen.it@gmail.com' bằng email bạn dùng để đăng ký tài khoản Filament.
     */
    /**private function isAdmin(User $user): bool
    {
        return $user->email === 'admin';
    }
    */

    // Cấp toàn quyền cho Admin chặn trước mọi rules khác
    public function before(User $user, string $ability): bool|null
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null; // Trả về null để nó tiếp tục xét các quyền bên dưới nếu không phải Admin
    }

    /**
     * Determine whether the user can view any models.
     * Ai cũng có thể xem danh sách tài khoản.
     */
    public function viewAny(User $user): bool
    {
        return true; // Ai login vào cũng thấy danh sách
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Account $account): bool
    {
        return true; // Cho phép xem chi tiết nếu cần
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin(); // Chỉ admin mới thấy nút "New Account"
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Account $account): bool
    {
        return $user->isAdmin(); // Chỉ admin mới được sửa (Edit)
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Account $account): bool
    {
        return $user->isAdmin(); // Chỉ admin mới thấy nút xóa
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Account $account): bool
    {
        return $user->isAdmin(); // Chỉ admin mới được khôi phục
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Account $account): bool
    {
        return $user->isAdmin(); // Chỉ admin mới được xóa vĩnh viễn
    }
}
