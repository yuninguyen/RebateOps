<?php

namespace App\Observers;

use App\Models\PayoutLog;
use App\Services\GoogleSheetService;
use App\Jobs\SyncGoogleSheetJob;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayoutLogObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Sự kiện SAVED: Chạy sau khi bản ghi được Lưu (cả Create và Update)
     */
    public function saved(PayoutLog $payoutLog): void
    {
        if ($payoutLog->transaction_type === 'liquidation' && $payoutLog->parent_id) {
            $parent = $payoutLog->parent;
            if ($parent) {
                if ($payoutLog->wasChanged(['exchange_rate', 'total_vnd']) || $payoutLog->wasRecentlyCreated) {
                    $parent->updateQuietly([
                        'exchange_rate' => $payoutLog->exchange_rate,
                        'total_vnd' => $payoutLog->total_vnd,
                    ]);

                    // ✅ ĐỒNG BỘ PARENT LÊN GOOGLE SHEETS
                    // Vì dùng updateQuietly nên Observer của Parent không tự chạy, ta phải gọi thủ công
                    \App\Jobs\SyncGoogleSheetJob::dispatch($parent->id, get_class($parent));
                }
            }
        }

        if ($payoutLog->is_syncing_from_sheet) {
            return;
        }

        \App\Jobs\SyncGoogleSheetJob::dispatch($payoutLog->id, get_class($payoutLog));
    }

    /**
     * Sự kiện CREATED: Xử lý nếu đơn vừa tạo đã ở trạng thái Completed
     */
    public function created(PayoutLog $payoutLog): void
    {
        if ($payoutLog->status === 'completed') {
            $this->syncMethodBalance($payoutLog->payoutMethod);
            
            if ($payoutLog->transaction_type === 'liquidation') {
                $this->updateMethodExchangeRate($payoutLog->payoutMethod);
            }
        }
    }

    /**
     * Sự kiện UPDATED: Xử lý thay đổi Status để cộng/trừ tiền ví
     */
    public function updated(PayoutLog $payoutLog): void
    {
        // 1. Kiểm tra nếu có sự thay đổi trạng thái (vào hoặc ra khỏi Completed)
        $wasCompleted = $payoutLog->getOriginal('status') === 'completed';
        $isCompleted = $payoutLog->status === 'completed';

        if ($wasCompleted || $isCompleted) {
            $this->syncMethodBalance($payoutLog->payoutMethod);

            // 2. Nếu là Liquidation và thay đổi dữ liệu liên quan đến tỷ giá
            if ($payoutLog->transaction_type === 'liquidation') {
                $this->updateMethodExchangeRate($payoutLog->payoutMethod);
            }
        }
    }

    /**
     * Sự kiện DELETED: Hoàn lại tiền nếu đơn bị xóa là đơn đã hoàn thành
     */
    public function deleted(PayoutLog $payoutLog): void
    {
        if ($payoutLog->status === 'completed') {
            $this->syncMethodBalance($payoutLog->payoutMethod);
            
            if ($payoutLog->transaction_type === 'liquidation') {
                $this->updateMethodExchangeRate($payoutLog->payoutMethod);
            }
        }

        // Luôn đồng bộ lệnh xóa lên Google Sheet
        \App\Jobs\SyncGoogleSheetJob::dispatch($payoutLog->id, get_class($payoutLog), 'delete');
    }

    /**
     * Đồng bộ số dư ví bằng cách tính lại tổng các giao dịch (Chống nhân đôi lỗi)
     */
    private function syncMethodBalance(?\App\Models\PayoutMethod $method): void
    {
        if (!$method) return;

        DB::transaction(function () use ($method) {
            // Lock ví để tránh race condition
            $method = $method->lockForUpdate()->find($method->id);

            // TỔNG RÚT (WITHDRAWAL) - Dùng net_amount_usd (tiền thực nhận)
            $totalWithdraw = PayoutLog::where('payout_method_id', $method->id)
                ->where('transaction_type', 'withdrawal')
                ->where('status', 'completed')
                ->sum('net_amount_usd') ?? 0;

            // TỔNG ĐỔI (LIQUIDATION) - Dùng amount_usd (tiền gốc bán ra)
            $totalLiquidate = PayoutLog::where('payout_method_id', $method->id)
                ->where('transaction_type', 'liquidation')
                ->where('status', 'completed')
                ->sum('amount_usd') ?? 0;

            // Số dư = Thu - Chi
            $balance = $totalWithdraw - $totalLiquidate;

            $method->updateQuietly(['current_balance' => round($balance, 2)]);
        });
    }

    /**
     * Tự động tính tỷ giá trung bình (VWAP) cho ví dựa trên các đơn Liquidation đã hoàn thành
     */
    private function updateMethodExchangeRate(?\App\Models\PayoutMethod $method): void
    {
        if (!$method) return;

        // Công thức: Tỷ giá TB = SUM(amount_usd * exchange_rate) / SUM(amount_usd)
        $data = PayoutLog::where('payout_method_id', $method->id)
            ->where('transaction_type', 'liquidation')
            ->where('status', 'completed')
            ->selectRaw('SUM(amount_usd * exchange_rate) as total_weighted, SUM(amount_usd) as total_usd')
            ->first();

        $averageRate = 0;
        if ($data && $data->total_usd > 0) {
            $averageRate = $data->total_weighted / $data->total_usd;
        }

        // Cập nhật vào ví (dùng updateQuietly để tránh lặp)
        $method->updateQuietly(['exchange_rate' => round($averageRate, 2)]);
    }
}
