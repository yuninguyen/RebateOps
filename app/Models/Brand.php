<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity; // Bật tính năng Log
use Spatie\Activitylog\LogOptions;          // Tùy chọn Log

class Brand extends Model
{
    use LogsActivity; // Kích hoạt máy quay cho Brand

    // Cho phép lưu các cột này
    protected $fillable = [
        'name',
        'platform',
        'slug',
        'boost_percentage',
        'maximum_limit',
    ];

    // Cấu hình theo dõi toàn bộ các cột được phép điền
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
