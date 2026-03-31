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
        // Khởi tạo Query cơ bản
        $query = PayoutLog::query();

        // 3. QUAN TRỌNG: Nếu có User được chọn, hãy lọc dữ liệu của User đó
        if (!auth()->user()?->isAdmin()) {
            $query->whereHas('account', function ($q) {
                $q->where('user_id', auth()->id());
            });
        } elseif ($this->selectedUserId) {
            $query->whereHas('account', function ($q) {
                $q->where('user_id', $this->selectedUserId);
            });
        }

        // Tính toán dựa trên Query đã được lọc (hoặc chưa lọc nếu selectedUserId là null)
        $totalCompletedUsd = (clone $query)->where('status', 'completed')->sum('net_amount_usd');
        $pendingCount = (clone $query)->where('status', 'pending')->count();
        $totalVnd = (clone $query)->where('status', 'completed')
            ->where('transaction_type', 'liquidation')
            ->sum('total_vnd');

        // Hiển thị nhãn để biết đang lọc hay xem tổng
        $labelSuffix = $this->selectedUserId ? ' (Filtered)' : ' (Global)';

        return [
            Stat::make('Total Paid (USD)' . $labelSuffix, '$' . number_format($totalCompletedUsd, 2))
                ->description('Completed payouts')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Pending Requests' . $labelSuffix, $pendingCount)
                ->description('Awaiting processing')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Total Exchanged (VND)' . $labelSuffix, number_format($totalVnd, 0, ',', '.') . ' ₫')
                ->description('Converted to VND')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
        ];
    }
}
