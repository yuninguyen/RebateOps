<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutMethod extends Model
{
    use HasFactory;

    // Tên bảng trong Database (để chắc chắn không lệch)
    protected $table = 'payout_methods';

    // Cho phép lưu các trường này vào Database
    protected $fillable = [
        'name',
        'type',
        'current_balance',
        'email',
        'password',
        'paypal_account',
        'paypal_password',
        'full_name',
        'dob',
        'ssn',
        'phone',
        'address',
        'auth_code',
        'question_1',
        'answer_1',
        'question_2',
        'answer_2',
        'proxy_type',
        'ip_address',
        'location',
        'isp',
        'browser',
        'device',
        'status',
        'note',
        'is_active', // 🟢 Thêm dòng này để điều khiển được công tắc bật/tắt
    ];

    // Quan hệ với bảng Logs (để sau này tính toán số dư)
    public function payoutLogs(): HasMany
    {
        return $this->hasMany(PayoutLog::class);
    }
}
