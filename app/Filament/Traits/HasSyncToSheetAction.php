<?php

namespace App\Filament\Traits;

use App\Services\GoogleSyncService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

trait HasSyncToSheetAction
{
    /**
     * Tạo Action chuẩn để đồng bộ dữ liệu lên Google Sheet
     * $serviceMethod: Tên hàm trong GoogleSyncService (vd: 'syncEmails', 'syncTrackers'...)
     * $modelLabel: Tên Model tiếng Việt/Anh để hiển thị thông báo (vd: 'Emails', 'Trackers'...)
     */
    protected function getSyncToSheetAction(string $serviceMethod, string $modelLabel): Action
    {
        return Action::make('sync_to_google_sheet')
            ->label(__('system.notifications.sync_to_google_sheet'))
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading(__('system.notifications.sync_to_google_sheet'))
            ->modalDescription(__('system.notifications.sync_confirm_msg'))
            ->modalSubmitActionLabel(__('system.account_claim.submit')) // Dùng chung nút confirm
            // 🟢 CHỈ HIỆN CHO ADMIN
            ->visible(fn() => auth()->user()?->isAdmin())
            ->action(function () use ($serviceMethod, $modelLabel) {
                try {
                    // 1. Lấy dữ liệu thực tế đang hiển thị (quan trọng: tôn trọng Filter)
                    $records = $this->getFilteredTableQuery()->get();

                    if ($records->isEmpty()) {
                        Notification::make()
                            ->title(__('system.notifications.no_records_found'))
                            ->body(__('system.notifications.no_records_to_sync'))
                            ->warning()
                            ->send();
                        return;
                    }

                    // 2. Gọi Service xử lý
                    $syncService = app(GoogleSyncService::class);
                    $syncService->$serviceMethod($records);

                    // 3. Thông báo thành công
                    Notification::make()
                        ->title(__('system.notifications.sync_success'))
                        ->body(__('system.notifications.sync_success_msg', ['count' => count($records)]))
                        ->success()
                        ->send();

                } catch (\Exception $e) {
                    Log::error("Manual Sync Error [{$modelLabel}]: " . $e->getMessage());

                    Notification::make()
                        ->title(__('system.notifications.sync_error'))
                        ->body($e->getMessage())
                        ->danger()
                        ->persistent()
                        ->send();
                }
            });
    }
}
