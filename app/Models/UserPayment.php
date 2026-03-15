<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserPayment extends Model
{
    use HasFactory;

    // Các cột được phép lưu dữ liệu (Mass Assignment)
    protected $fillable = [
        'user_id',
        'platform',
        'transaction_type',
        'total_usd',
        'exchange_rate',
        'total_vnd',
        'status', // pending, paid
        'payment_proof',
        'note',
    ];

    // 🟢 Mối quan hệ: 1 Phiếu lương thuộc về 1 User
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // 🟢 Mối quan hệ: 1 Phiếu lương chứa NHIỀU đơn Payout Logs con
    public function payoutLogs(): HasMany
    {
        return $this->hasMany(PayoutLog::class);
    }
}
