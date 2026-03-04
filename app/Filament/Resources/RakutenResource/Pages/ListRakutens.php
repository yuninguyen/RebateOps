<?php

namespace App\Filament\Resources\RakutenResource\Pages;

use App\Filament\Resources\RakutenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListRakutens extends ListRecords
{
    protected static string $resource = RakutenResource::class;

    // Thêm hàm này để bảng giãn rộng ra toàn màn hình
    public function getMaxWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [

            // Nút Import
            \Filament\Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\AccountImporter::class)
                ->label('Import All Data')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray'),

            // Nút Export
            \Filament\Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\AccountExporter::class)
                ->label('Export All Data')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray'),

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

                    $targetTab = 'Rakuten_Accounts'; // Xác định tên Tab
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

            \Filament\Actions\CreateAction::make(),
        ];
    }
}
