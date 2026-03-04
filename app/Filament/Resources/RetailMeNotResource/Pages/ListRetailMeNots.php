<?php

namespace App\Filament\Resources\RetailMeNotResource\Pages;

use App\Filament\Resources\RetailMeNotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRetailMeNots extends ListRecords
{
    protected static string $resource = RetailMeNotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Nút Sync to Google Sheet
            Actions\Action::make('sync_this_platform')
                ->label('Sync to Google Sheet')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->action(function () {
                    $records = $this->getFilteredTableQuery()->get();
                    if ($records->isEmpty()) return;

                    $rows = $records->map(fn($record) => static::$resource::formatAccountForSheet($record))->toArray();

                    $targetTab = 'RetailMeNot_Accounts'; // Xác định tên Tab
                    $sheetService = app(\App\Services\GoogleSheetService::class);

                    // Thực thi Upsert
                    $result = $sheetService->upsertRows($rows, $targetTab);

                    // --- CẬP NHẬT INDEX CLIP THEO DANH SÁCH 19 CỘT ---
                    // Cột F (Note Email): Index 5
                    $sheetService->formatColumnsAsClip($targetTab, 5, 6);
                    // Cột O (Platform Note): Index 14
                    $sheetService->formatColumnsAsClip($targetTab, 14, 15);
                    // Cột R (Personal Info): Index 17
                    $sheetService->formatColumnsAsClip($targetTab, 17, 18);

                    \Filament\Notifications\Notification::make()
                        ->title("Synced to {$targetTab}!") // ✅ ĐÃ SỬA: Dùng tên Tab thay vì biến mảng $result
                        ->body("Updated: {$result['updated']} | Appended: {$result['appended']}")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make(),
        ];
    }
}
