<?php

namespace App\Filament\Resources\JoinHoneyResource\Pages;

use App\Filament\Resources\JoinHoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJoinHoneys extends ListRecords
{
    protected static string $resource = JoinHoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Nút Sync to Google Sheet
            Actions\Action::make('sync_this_platform')
                ->label('Sync to Google Sheet')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                // 🟢 CHỈ HIỆN CHO ADMIN
                ->visible(fn() => auth()->user()?->isAdmin())
                ->requiresConfirmation()
                ->action(function () {
                    $records = $this->getFilteredTableQuery()->get();
                    if ($records->isEmpty()) return;

                    $rows = $records->map(fn($record) => static::$resource::formatAccountForSheet($record))->toArray();

                    $targetTab = 'JoinHoney_Accounts'; // Xác định tên Tab
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
