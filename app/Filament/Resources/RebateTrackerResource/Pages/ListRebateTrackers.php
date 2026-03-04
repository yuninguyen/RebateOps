<?php

namespace App\Filament\Resources\RebateTrackerResource\Pages;

use App\Filament\Resources\RebateTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRebateTrackers extends ListRecords
{
    protected static string $resource = RebateTrackerResource::class;

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
                ->action(function ($livewire) {
                    // 1. LẤY QUERY CỦA BẢNG ĐÃ ÁP DỤNG BỘ LỌC HIỆN TẠI
                    $query = $livewire->getFilteredTableQuery();
                    $records = $query->get();

                    if ($records->isEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title('No data to sync!')
                            ->warning()
                            ->send();
                        return;
                    }

                    // 2. CHẠY VÒNG LẶP QUA CỖ MÁY ĐỊNH DẠNG
                    $rows = [];
                    foreach ($records as $record) {
                        // SỬA Ở ĐÂY: Gọi đích danh RebateTrackerResource thay vì dùng static::
                        $rows[] = RebateTrackerResource::formatRecordForSheet($record);
                    }

                    // 3. GỌI SERVICE ĐỂ ĐẨY LÊN SHEET BẰNG UPSERT
                    $sheetService = app(\App\Services\GoogleSheetService::class);
                    $result = $sheetService->upsertRows($rows, 'All_Rebate_Tracker');

                    // 4. ÉP ĐỊNH DẠNG CLIP CHO CỘT NOTE VÀ DETAIL
                    $sheetService->formatColumnsAsClip('All_Rebate_Tracker', 5, 6);   // Note Email
                    $sheetService->formatColumnsAsClip('All_Rebate_Tracker', 15, 16); // Note Platform
                    $sheetService->formatColumnsAsClip('All_Rebate_Tracker', 18, 19); // Personal Info

                    // 5. THÔNG BÁO THÀNH CÔNG
                    \Filament\Notifications\Notification::make()
                        ->title('Synchronization successful!')
                        ->body("Scanned {$records->count()} record(s). Updated {$result['updated']} old row và added {$result['appended']} new row.")
                        ->success()
                        ->send();
                }),

            // 2. NÚT TẠO MỚI MẶC ĐỊNH CỦA FILAMENT (MÀU CAM)
            Actions\CreateAction::make(),
        ];
    }
}
