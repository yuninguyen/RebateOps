<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    public $timestamps = true; // Đảm bảo vẫn dùng timestamp

    // Cho phép điền dữ liệu vào các cột này
    protected $fillable = [
        'platform',
        'email_id', // Cột liên kết với bảng emails
        'password',
        'state',
        'device',
        'paypal_info',
        'device_linked_paypal',
        'account_created_at', // Cho phép điền ngày tạo
        'paypal_linked_at', // Cho phép điền ngày cập nhật
        'status',
        'note',
        'user_id', // Cột liên kết với bảng users
    ];

    protected $casts = [
        // Lưu ý: Nếu cột status trong DB là JSON/Text thì để 'array', 
        // nhưng nếu là string (active/banned) thì nên bỏ dòng này.
        'status' => 'array',
        'paypal_linked_at' => 'date',
        'account_created_at' => 'date',
    ];


    /**
     * Khai báo mối quan hệ: Một tài khoản thuộc về một Người dùng (Holder)
     */
    public function user(): BelongsTo
    {
        // Chỉ giữ lại 1 hàm duy nhất.
        // Thêm 'user_id' để chắc chắn nó tìm đúng cột liên kết.
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * MỚI: Liên kết tài khoản này với một Email gốc
     */
    public function email(): BelongsTo
    {
        return $this->belongsTo(Email::class, 'email_id');
    }
}
