<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB; // Cần thiết để dùng các hàm tính toán của Database

class AccountOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Khởi tạo mảng để chứa các thẻ (Widget Cards)
        $stats = [];

        // 1. Tạo 1 thẻ đầu tiên để đếm TỔNG SỐ TẤT CẢ TÀI KHOẢN (Tùy chọn)
        $stats[] = Stat::make('Total Account', Account::count())
            ->description('The entire system')
            ->descriptionIcon('heroicon-m-users')
            ->color('success');

        // 2. Query phân tích: Gom tất cả account lại theo nhóm Platform và đếm số lượng
        $platformCounts = Account::select('platform', DB::raw('count(*) as total'))
            ->groupBy('platform')
            ->get();

        // 3. Dùng vòng lặp: Cứ tìm thấy 1 Platform thì tự động "đẻ" ra 1 thẻ mới
        foreach ($platformCounts as $row) {
            
            // Xử lý trường hợp có tài khoản bạn quên chưa nhập tên Platform
            $platformName = $row->platform ?: 'Uncategorized';

            $stats[] = Stat::make($platformName, $row->total)
                ->description('Account')
                ->descriptionIcon('heroicon-m-user-circle')
                ->color('primary');
        }

        return $stats; // Trả về danh sách các thẻ để hiển thị
    }
}