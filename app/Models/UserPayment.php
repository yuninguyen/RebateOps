<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity; // Bật tính năng Log
use Spatie\Activitylog\LogOptions;          // Tùy chọn Log

class UserPayment extends Model
{
    use LogsActivity, HasFactory, SoftDeletes; // Kích hoạt "máy quay" và xóa mềm

    protected static function booted()
    {
        static::deleting(function ($payment) {
            // 🟢 TỰ ĐỘNG GIẢI PHÓNG: Khi xóa phiếu lương, các đơn rút tiền con sẽ quay về "Chưa chốt sổ" (Pending)
            $payment->payoutLogs()->update(['user_payment_id' => null]);
        });
    }

    // Các cột được phép lưu dữ liệu (Mass Assignment)
    protected $fillable = [
        'user_id',
        'platform',
        'transaction_type',
        'total_usd',
        'exchange_rate',
        'payout_rate',
        'payout_percentage',
        'total_vnd',
        'profit_vnd',
        'status', // pending, paid
        'payment_proof',
        'note',
    ];

    // Cấu hình máy quay: Báo nó theo dõi cái gì
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable() // Theo dõi mọi sự thay đổi của các cột trong $fillable
            ->logOnlyDirty() // Chỉ ghi lại nếu thực sự có sửa đổi
            ->dontSubmitEmptyLogs();
    }

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
