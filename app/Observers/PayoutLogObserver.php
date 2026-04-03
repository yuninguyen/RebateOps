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
                }
            }
        }

        if (isset($payoutLog->is_syncing_from_sheet) && $payoutLog->is_syncing_from_sheet) {
            return;
        }

        \App\Jobs\SyncGoogleSheetJob::dispatch($payoutLog->id, get_class($payoutLog));
    }

    /**
     * Sự kiện UPDATED: Xử lý thay đổi Status để cộng/trừ tiền ví
     * FIX #1: Hỗ trợ rollback khi chuyển ngược status (completed → pending)
     * FIX #2: Dùng DB::transaction + lockForUpdate để tránh race condition
     */
    public function updated(PayoutLog $payoutLog): void
    {
        if (!$payoutLog->wasChanged('status')) {
            return;
        }

        $oldStatus = $payoutLog->getOriginal('status');
        $newStatus = $payoutLog->status;
        $method = $payoutLog->payoutMethod;

        if (!$method) {
            return;
        }

        DB::transaction(function () use ($payoutLog, $oldStatus, $newStatus, $method) {
            // Lock ví để tránh race condition khi nhiều giao dịch cùng lúc
            $method = $method->lockForUpdate()->find($method->id);

            // Rollback: completed → khác (hoàn lại balance)
            if ($oldStatus === 'completed' && $newStatus !== 'completed') {
                if ($payoutLog->transaction_type === 'withdrawal') {
                    $method->decrement('current_balance', $payoutLog->net_amount_usd);
                } elseif (in_array($payoutLog->transaction_type, ['hold', 'liquidation'])) {
                    $method->increment('current_balance', $payoutLog->amount_usd);
                }
            }

            // Forward: khác → completed (áp dụng balance)
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                if ($payoutLog->transaction_type === 'withdrawal') {
                    $method->increment('current_balance', $payoutLog->net_amount_usd);
                } elseif (in_array($payoutLog->transaction_type, ['hold', 'liquidation'])) {
                    $method->decrement('current_balance', $payoutLog->amount_usd);
                }
            }
        });
    }

    /**
     * Sự kiện DELETED: Cập nhật lại Sheet khi xóa dòng
     */
    public function deleted(PayoutLog $payoutLog): void
    {
        \App\Jobs\SyncGoogleSheetJob::dispatch($payoutLog->id, get_class($payoutLog), 'delete');
    }
}
