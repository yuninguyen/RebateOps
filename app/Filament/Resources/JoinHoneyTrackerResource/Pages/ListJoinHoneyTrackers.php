<?php

namespace App\Filament\Resources\JoinHoneyTrackerResource\Pages;

use App\Filament\Resources\JoinHoneyTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\GoogleSheetService;

class ListJoinHoneyTrackers extends ListRecords
{
    protected static string $resource = JoinHoneyTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. NÚT SYNC ALL DATA MỚI ĐƯỢC CHUYỂN LÊN ĐÂY
            Actions\Action::make('sync_all_filtered_data')
                ->label('Sync All Data to Sheet')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Synchronize data to Google Sheets')
                ->modalDescription('The system will synchronize all records currently displayed on the table (with filters applied) to Google Sheets. Are you sure you want to continue?')
                ->action(function () {
                    // 🟢 BƯỚC 1: CHỈ CẦN SỬA TÊN PLATFORM Ở ĐÂY CHO TỪNG FILE
                    $platformName = 'JoinHoney'; // Ví dụ: 'RetailMeNot', 'JoinHoney'...

                    $sheetService = app(GoogleSheetService::class);
                    $resource = static::getResource();

                    // 2. Lấy tất cả bản ghi thuộc Platform này
                    $records = \App\Models\RebateTracker::whereHas('account', function ($q) use ($platformName) {
                        $q->where('platform', $platformName);
                    })->get();

                    if ($records->isEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title('No data found to sync!')
                            ->warning()
                            ->send();
                        return;
                    }

                    // 3. Format dữ liệu
                    $rows = $records->map(
                        fn($record) =>
                        $resource::formatRecordForSheet($record)
                    )->toArray();

                    // 4. Đẩy lên Tab riêng và Tab tổng
                    $targetTab = $platformName . '_Tracker';
                    $sheetService->upsertRows($rows, $targetTab);
                    $sheetService->upsertRows($rows, 'All_Rebate_Tracker');

                    // 5. Định dạng Clip (Cột 16-18 là Note và Detail)                  
                    $sheetService->formatColumnsAsClip($targetTab, 5, 6);               // Note Email
                    $sheetService->formatColumnsAsClip('All_Rebate_Tracker', 5, 6);     // Note Email
                    $sheetService->formatColumnsAsClip($targetTab, 15, 16);             // Note Platform
                    $sheetService->formatColumnsAsClip('All_Rebate_Tracker', 15, 16);   // Note Platform
                    $sheetService->formatColumnsAsClip($targetTab, 18, 19);             // Personal Info
                    $sheetService->formatColumnsAsClip('All_Rebate_Tracker', 18, 19);   // Personal Info

                    // 6. THÔNG BÁO THÀNH CÔNG
                    \Filament\Notifications\Notification::make()
                        ->title('Synchronization successful!')
                        ->body("Synced " . count($rows) . " record(s) to Google Sheets.")
                        ->success()
                        ->send();
                }),
            // 2. NÚT TẠO MỚI MẶC ĐỊNH CỦA FILAMENT (MÀU CAM)
            Actions\CreateAction::make(),
        ];
    }
}
