<?php

namespace App\Filament\Widgets;

use App\Models\PayoutLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Livewire\Attributes\On;

class PayoutStats extends BaseWidget
{
    // 1. Phải khai báo biến này ở đây để lưu trữ ID User khi nhận được từ Table
    public ?int $selectedUserId = null;

    // 🟢 Đổi từ '10s' thành null để tắt Polling. 
    // Data sẽ cập nhật khi User F5 hoặc có tương tác.
    protected static ?string $pollingInterval = null;

    // Thêm hiệu ứng làm mờ khi đang tải (Loading state)
    protected static bool $isLazy = false;

    // 2. Lắng nghe sự kiện từ Table truyền lên
    #[On('updateStatsUser')]
    public function updateUserId($userId): void
    {
        $this->selectedUserId = $userId;
        // Tín hiệu này sẽ tự động kích hoạt hàm getStats() chạy lại
    }

    protected function getStats(): array
    {
        // 1. Khởi tạo Query cho RebateTracker (Dành cho Card 1 - Confirmed)
        // Đây là nguồn dữ liệu chính xác nhất cho doanh thu đã xác nhận từ platform
        $rebateQuery = \App\Models\RebateTracker::query();
        
        // 2. Khởi tạo Query cho UserPayment (Dành cho Card 2 & 3 - Paid/Exchanged)
        // Đây là nguồn dữ liệu cho các khoản đã thực sự chi trả cho User
        $disbursementQuery = \App\Models\UserPayment::query();

        // 3. Áp dụng logic lọc theo User cho cả hai Query
        if (!auth()->user()?->isAdmin()) {
            // Nếu là Staff, chỉ xem của chính mình
            $rebateQuery->where('user_id', auth()->id());
            $disbursementQuery->where('user_id', auth()->id());
        } elseif ($this->selectedUserId) {
            // Nếu là Admin và đang chọn 1 User cụ thể
            $rebateQuery->where('user_id', $this->selectedUserId);
            $disbursementQuery->where('user_id', $this->selectedUserId);
        }

        // --- TÍNH TOÁN CARD 1: CONFIRMED (Từ RebateTracker) ---
        // Đồng bộ 100% với bảng Revenue Report bên dưới
        $totalConfirmedUsd = (clone $rebateQuery)->where('status', 'Confirmed')
            ->sum('rebate_amount');
            
        // --- TÍNH TOÁN CARD 2 & 3: PAID / EXCHANGED (Từ UserPayment/Disbursement) ---
        $totalPaidUsd = (clone $disbursementQuery)->where('status', 'paid')
            ->sum('total_usd');

        $totalVnd = (clone $disbursementQuery)->where('status', 'paid')
            ->sum('total_vnd');

        // Hiển thị nhãn để biết đang lọc hay xem tổng
        $labelSuffix = $this->selectedUserId ? ' (Filtered)' : ' (Global)';

        return [
            Stat::make('Total CONFIRMED' . $labelSuffix, '$' . number_format($totalConfirmedUsd, 2))
                ->description('Cashback confirmed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Total Paid (USD)' . $labelSuffix, '$' . number_format($totalPaidUsd, 2))
                ->description('Completed payments')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Total Exchanged (VND)' . $labelSuffix, number_format($totalVnd, 0, ',', '.') . ' ₫')
                ->description('Converted to VND')
                ->descriptionIcon('heroicon-m-arrows-right-left')
                ->color('info'),
        ];
    }
}
