<?php

namespace App\Observers;

use App\Models\PayoutLog;
use App\Services\GoogleSheetService;
use App\Jobs\SyncGoogleSheetJob;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Log;

class PayoutLogObserver implements ShouldHandleEventsAfterCommit
{
    protected $sheetService;

    public function __construct(GoogleSheetService $sheetService)
    {
        $this->sheetService = $sheetService;
    }

    /**
     * Sự kiện SAVED: Chạy sau khi bản ghi được Lưu (cả Create và Update)
     */
    public function saved(PayoutLog $payoutLog): void
    {
        // 🟢 1. LOGIC ĐỒNG BỘ NỘI BỘ (DATABASE)
        // Nếu là dòng thanh khoản (liquidation), cập nhật tỷ giá và VND cho dòng cha
        if ($payoutLog->transaction_type === 'liquidation' && $payoutLog->parent_id) {
            $parent = $payoutLog->parent;
            if ($parent) {
                // Chỉ update nếu có sự thay đổi về tiền tệ
                if ($payoutLog->wasChanged(['exchange_rate', 'total_vnd']) || $payoutLog->wasRecentlyCreated) {
                    $parent->updateQuietly([
                        'exchange_rate' => $payoutLog->exchange_rate,
                        'total_vnd' => $payoutLog->total_vnd,
                    ]);
                }
            }
        }

        // 🟢 2. ĐẨY JOB LÊN GOOGLE SHEETS
        // Thay vì gọi syncToSheet trực tiếp (làm chậm web), ta đẩy vào Job để chạy ngầm
        \App\Jobs\SyncGoogleSheetJob::dispatch($payoutLog->id, get_class($payoutLog));
    }

    /**
     * Sự kiện UPDATED: Xử lý thay đổi Status để cộng/trừ tiền ví
     */
    public function updated(PayoutLog $payoutLog): void
    {
        // 🟢 3. CẬP NHẬT BALANCE (Chỉ chạy khi status từ Pending -> Completed)
        if ($payoutLog->isDirty('status') && $payoutLog->status === 'completed') {
            $method = $payoutLog->payoutMethod;

            if ($method) {
                // Nếu là Withdrawal: Cộng tiền vào ví
                if ($payoutLog->transaction_type === 'withdrawal') {
                    $method->increment('current_balance', $payoutLog->net_amount_usd);
                }
                // Nếu là Liquidation: Trừ tiền khỏi ví (vì đã lấy tiền mặt VND)
                elseif ($payoutLog->transaction_type === 'liquidation') {
                    $method->decrement('current_balance', $payoutLog->amount_usd);
                }
            }
        }
    }

    /**
     * Sự kiện DELETED: Cập nhật lại Sheet khi xóa dòng
     */
    public function deleted(PayoutLog $payoutLog): void
    {
        \App\Jobs\SyncGoogleSheetJob::dispatch($payoutLog->id, get_class($payoutLog), 'delete');
    }

    /**
     * Hàm này bạn có thể gọi thủ công hoặc dùng trong Job
     * Đã cập nhật đủ 18 cột khớp với PayoutLogResource của bạn
     */
    public function syncToSheet()
    {
        try {
            $allPayouts = PayoutLog::with(['account.email', 'payoutMethod'])
                ->orderBy('created_at', 'desc')
                ->get();

            $rows = [
                ['ID', 'Date', 'Email', 'Platform', 'Wallet', 'Asset type', 'Gift Card Brand', 'Card number', 'PIN', 'Transaction type', 'Amount', 'Fee', 'Boost (%)', 'Net USD', 'Rate', 'VND', 'Status', 'Note']
            ];

            foreach ($allPayouts as $p) {
                $rows[] = [
                    (string) $p->id,
                    (string) $p->created_at->format('d/m/Y H:i'),
                    (string) ($p->account?->email?->email ?? 'N/A'),
                    (string) strtoupper($p->account?->platform ?? 'N/A'),
                    (string) ($p->payoutMethod?->name ?? ($p->asset_type === 'gift_card' ? 'In-Hand' : 'N/A')),
                    (string) strtoupper(str_replace('_', ' ', $p->asset_type ?? 'N/A')),
                    (string) ucfirst(str_replace('_', ' ', $p->gc_brand ?? 'N/A')),
                    (string) ($p->gc_code ?? 'N/A'),
                    (string) ($p->gc_pin ?? 'N/A'),
                    (string) ucfirst($p->transaction_type ?? 'N/A'),
                    (string) number_format($p->amount_usd, 2, '.', ''),
                    (string) number_format($p->fee_usd, 2, '.', ''),
                    (string) $p->boost_percentage . '%',
                    (string) number_format($p->net_amount_usd, 2, '.', ''),
                    (string) number_format($p->exchange_rate ?? 0, 0, '.', ','),
                    (string) number_format($p->total_vnd ?? 0, 0, '.', ','),
                    (string) ucfirst($p->status),
                    (string) ($p->note ?? ''),
                ];
            }

            $this->sheetService->updateSheet($rows, 'A1:R', 'Payout_Logs');
        } catch (\Exception $e) {
            Log::error("Google Sheet Sync Error: " . $e->getMessage());
        }
    }
}
