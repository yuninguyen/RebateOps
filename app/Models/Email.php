<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Email extends Model
{
    public $timestamps = false; // Tắt tự động cập nhật created_at và updated_at

    protected $casts = [
        'email_created_at' => 'date',
    ];

    // Cho phép lưu các cột này vào database
    protected $fillable = [
        'status',   //  Cột status để lưu trạng thái của email (active, disabled, locked)
        'email',    // Cột email để lưu địa chỉ email
        'email_password',   // Cột email_password để lưu mật khẩu email
        'recovery_email',   // Cột recovery_email để lưu email khôi phục (nếu có)
        'two_factor_code',  // Cột 2FA code để lưu mã xác thực hai yếu tố (nếu có)
        'email_created_at', // Cột ngày tạo email (nếu có)   
        'note', // Cột note để lưu ghi chú về email
        'provider', // Cột provider để lưu tên nhà cung cấp email (ví dụ: gmail, yahoo)
        'accounts_count', // Cột đếm số lượng accounts liên quan
        'accounts.platform', // Cột liệt kê số lượng accounts theo từng platform (ví dụ: Facebook, Instagram)
    ];

    protected static function booted()
    {
        static::creating(function ($emailModel) {
            // Nếu cột provider đang trống, hệ thống sẽ tự bóc tách từ email
            if (empty($emailModel->provider) && !empty($emailModel->email)) {
                $email = $emailModel->email;

                // Lấy phần domain sau dấu @ (ví dụ: gmail.com)
                $domain = substr(strrchr($email, "@"), 1);

                // Lấy tên provider (ví dụ: gmail)
                $providerName = explode('.', $domain)[0];

                // Lưu vào database dưới dạng chữ thường để đồng bộ
                $emailModel->provider = strtolower($providerName);
            }
        });
    }

    // Thiết lập quan hệ: Một Email có thể dùng cho NHIỀU Account Platform
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
