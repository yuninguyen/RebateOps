<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\GoogleSheetService;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncGoogleSheetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param int    $recordId   ID của bản ghi cần sync
     * @param string $modelClass Tên Class của Model (vd: PayoutLog::class)
     * @param string $action     'upsert' hoặc 'delete'
     */
    public function __construct(
        protected $recordId,
        protected $modelClass,
        protected string $action = 'upsert'
    ) {}

    public function handle(GoogleSheetService $service): void
    {
        try {
            $headers      = [];
            $formattedRow = [];
            $targetTabs   = [];

            // ── 1. Nếu là DELETE: cần biết $targetTabs trước, rồi mới xóa ──
            // Với delete, record đã bị xóa khỏi DB nên không load được relations.
            // Ta xác định tab dựa vào modelClass và recordId đã lưu trong job.
            if ($this->action === 'delete') {
                switch ($this->modelClass) {
                    case \App\Models\Email::class:
                        $targetTabs = ['Emails'];
                        break;
                    case \App\Models\Account::class:
                        // Không còn record → không biết platform, xóa tất cả tab _Accounts
                        // hoặc bạn có thể lưu thêm platform vào job. Hiện tại xóa theo ID trên tất cả tab tìm được.
                        $targetTabs = ['General_Accounts']; // fallback an toàn
                        break;
                    case \App\Models\RebateTracker::class:
                        $targetTabs = ['All_Rebate_Tracker']; // tab tổng luôn xóa được
                        break;
                    case \App\Models\PayoutLog::class:
                        $targetTabs = ['Payout_Logs'];
                        break;
                    case \App\Models\PayoutMethod::class:
                        $targetTabs = ['Payout_Methods'];
                        break;
                }

                foreach ($targetTabs as $tabName) {
                    $service->deleteRowsByIds([(string)$this->recordId], $tabName);
                }
                return;
            }

            // ── 2. Tìm bản ghi kèm relations ──
            $record = $this->getRecordWithRelations();

            if (!$record) {
                Log::warning("SyncGoogleSheetJob: Record not found [{$this->modelClass} #{$this->recordId}]");
                return;
            }

            // ── 3. MAPPING: Xác định Resource, Tab và format dữ liệu ──
            switch ($this->modelClass) {
                case \App\Models\Email::class:
                    $resource     = \App\Filament\Resources\EmailResource::class;
                    $targetTabs[] = 'Emails';
                    $headers      = $resource::$emailHeaders;
                    $formattedRow = $resource::formatEmailForSheet($record);
                    break;

                case \App\Models\Account::class:
                    $platform     = $record->platform ?: 'General';
                    $targetTabs[] = ucfirst($platform) . '_Accounts';
                    $headers      = \App\Filament\Resources\AccountResource::$accountHeaders;
                    $formattedRow = \App\Filament\Resources\AccountResource::formatAccountForSheet($record);
                    break;

                case \App\Models\RebateTracker::class:
                    $platform   = $record->account?->platform ?: 'General';
                    $targetTabs = ['All_Rebate_Tracker', ucfirst($platform) . '_Tracker'];
                    $headers    = \App\Filament\Resources\RebateTrackerResource::$trackerHeaders;
                    $formattedRow = \App\Filament\Resources\RebateTrackerResource::formatRecordForSheet($record);
                    break;

                case \App\Models\PayoutLog::class:
                    $resource     = \App\Filament\Resources\PayoutLogResource::class;
                    $targetTabs[] = 'Payout_Logs';
                    $headers      = $resource::$payoutLogHeaders;
                    $formattedRow = $resource::formatPayoutLogForSheet($record);
                    break;

                case \App\Models\PayoutMethod::class:
                    $resource     = \App\Filament\Resources\PayoutMethodResource::class;
                    $targetTabs[] = 'Payout_Methods';
                    $headers      = $resource::$payoutMethodHeaders;
                    $formattedRow = $resource::formatPayoutMethodForSheet($record);
                    break;

                default:
                    Log::warning("SyncGoogleSheetJob: Unhandled modelClass [{$this->modelClass}]");
                    return;
            }

            // ── 4. UPSERT vào từng tab ──
            if (!empty($targetTabs) && !empty($formattedRow)) {
                foreach ($targetTabs as $tabName) {
                    $service->createSheetIfNotExist($tabName);
                    $service->upsertRows([$formattedRow], $tabName, $headers);
                    $this->applySpecificFormatting($service, $tabName);
                }
            }
        } catch (\Exception $e) {
            Log::error("SyncGoogleSheetJob Error [{$this->modelClass} #{$this->recordId}]: " . $e->getMessage());
            throw $e; // Re-throw để queue có thể retry
        }
    }

    /**
     * Tự động load các quan hệ cần thiết tùy theo loại Model
     */
    protected function getRecordWithRelations()
    {
        $query = $this->modelClass::query();

        if ($this->modelClass === \App\Models\Account::class) {
            $query->with(['email', 'user']);
        } elseif ($this->modelClass === \App\Models\RebateTracker::class) {
            $query->with(['account.email', 'user']);
        } elseif ($this->modelClass === \App\Models\PayoutLog::class) {
            $query->with(['account.email', 'payoutMethod']);
        }

        return $query->find($this->recordId);
    }

    /**
     * Áp dụng định dạng đặc thù (màu, clip) sau khi sync xong 1 dòng
     */
    protected function applySpecificFormatting(GoogleSheetService $service, string $tabName): void
    {
        if ($tabName === 'Payout_Logs') {
            $service->formatColumnsAsClip($tabName, 16, 17);

        } elseif ($tabName === 'Payout_Methods') {
            $service->formatColumnsAsClip($tabName, 4, 8);
            $service->formatColumnsAsClip($tabName, 25, 26);
            $service->applyFormattingWithRules($tabName, 24, [
                'Limited' => ['red' => 1.0, 'green' => 0.8, 'blue' => 0.8],
            ]);

        } elseif ($tabName === 'Emails') {
            $service->formatColumnsAsClip($tabName, 2, 3);
            $service->applyFormattingWithRules($tabName, 1, [
                'Live'     => ['red' => 0.85, 'green' => 0.95, 'blue' => 0.85],
                'Disabled' => ['red' => 1.0,  'green' => 0.8,  'blue' => 0.8],
            ]);

        } elseif (str_ends_with($tabName, '_Accounts')) {
            $service->formatColumnsAsClip($tabName, 5, 6);
            $service->formatColumnsAsClip($tabName, 14, 15);
            $service->formatColumnsAsClip($tabName, 17, 18);

        } elseif (str_contains($tabName, '_Tracker')) {
            $service->formatColumnsAsClip($tabName, 5, 6);
            $service->formatColumnsAsClip($tabName, 15, 16);
            $service->formatColumnsAsClip($tabName, 18, 19);
        }
    }
}
