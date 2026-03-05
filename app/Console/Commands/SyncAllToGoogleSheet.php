<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncAllToGoogleSheet extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // Tên lệnh bạn sẽ gõ trên terminal
    protected $signature = 'sync:all';

    /**
     * The console command description.
     *
     * @var string
     */

    // Mô tả lệnh
    protected $description = 'Refresh all data from the database to Google Sheets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Initiating the full-scale synchronization process...');

        // 1. Đồng bộ Emails
        $this->syncTask('Emails', \App\Filament\Resources\EmailResource::class);

        // 2. Đồng bộ Payout Methods (Ví)
        $this->syncTask('Payout Methods', \App\Filament\Resources\PayoutMethodResource::class);

        // 3. Đồng bộ Payout Logs (Giao dịch)
        $this->syncTask('Payout Logs', \App\Filament\Resources\PayoutLogResource::class);

        // 4. Đồng bộ Accounts (Chia theo Platform)
        $this->info('⏳ Processing accounts (this may take time due to multiple tabs)...');
        $this->syncAccounts();

        // 5. Đồng bộ Rebate Trackers
        $this->info('⏳ Processing Rebate Trackers...');
        $this->syncTrackers();

        $this->newLine();
        $this->info('✅ ALL DATA HAS BEEN UPDATED ON GOOGLE SHEETS!');
    }

    /**
     * Hàm phụ trợ chạy các Resource có sẵn hàm syncToGoogleSheet
     */
    protected function syncTask($name, $resourceClass)
    {
        $this->comment(" Syncing to Google Sheets: {$name}...");
        try {
            $resourceClass::syncToGoogleSheet();
            $this->info("   ✔ {$name} done!");
        } catch (\Exception $e) {
            $this->error("   ✘ Error {$name}: " . $e->getMessage());
        }
    }

    /**
     * Logic riêng cho Account vì cần chia Tab
     */
    protected function syncAccounts()
    {
        try {
            $accounts = \App\Models\Account::all();
            $grouped = $accounts->groupBy('platform');
            foreach ($grouped as $platform => $items) {
                $platformName = $platform ?: 'General';
                $this->line("   -> Pushing tabs: " . ucfirst($platformName) . "_Accounts");
                
                // Sau: gom toàn bộ rows rồi gọi 1 lần duy nhất ✅
$rows = $items->map(fn($item) => AccountResource::formatAccountForSheet($item))->toArray();
$sheetService->upsertRows($rows, $tabName, AccountResource::$accountHeaders);
// = 1 readSheet + 1 batchUpdate, dù có 100 accounts
            }
            $this->info("   ✔ Accounts done!");
        } catch (\Exception $e) {
            $this->error("   ✘ Error Accounts: " . $e->getMessage());
        }
    }

    /**
     * Logic riêng cho Tracker
     */
    protected function syncTrackers()
    {
        try {
            $sheetService = app(\App\Services\GoogleSheetService::class);
            // Load quan hệ sâu
            $trackers = \App\Models\RebateTracker::with(['account.email', 'user'])->get();

            if ($trackers->isEmpty()) return;

            // 1. Đẩy Tab TỔNG (All_Rebate_Tracker) - 1 Request duy nhất
            $this->line("   -> Pushing tabs: All_Rebate_Tracker");
            $allRows = $trackers->map(fn($t) => \App\Filament\Resources\RebateTrackerResource::formatRecordForSheet($t))->toArray();
            $sheetService->upsertRows($allRows, 'All_Rebate_Tracker', \App\Filament\Resources\RebateTrackerResource::$trackerHeaders);

            // 2. Đẩy các Tab RIÊNG theo Platform
            $grouped = $trackers->groupBy(fn($t) => $t->account?->platform ?: 'General');
            foreach ($grouped as $platform => $items) {
                $tabName = ucfirst($platform) . "_Tracker";
                $this->line("   -> Pushing tabs: {$tabName}");

                $rows = $items->map(fn($t) => \App\Filament\Resources\RebateTrackerResource::formatRecordForSheet($t))->toArray();
                $sheetService->upsertRows($rows, $tabName, \App\Filament\Resources\RebateTrackerResource::$trackerHeaders);
            }
            $this->info("   ✔ Trackers done!");
        } catch (\Exception $e) {
            $this->error("   ✘ Error Trackers: " . $e->getMessage());
        }
    }

    // --- KẾT THÚC ---
}
