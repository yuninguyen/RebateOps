<?php

namespace App\Filament\Resources\EmailResource\Pages;

use App\Filament\Resources\EmailResource;
use App\Filament\Exports\EmailExporter;
use App\Filament\Imports\EmailImporter;
use Illuminate\Support\HtmlString;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables;
use Filament\Notifications\Notification;
use App\Services\GoogleSheetService;

class ListEmails extends ListRecords
{
    protected static string $resource = EmailResource::class;

    public function getMaxContentWidth(): string
    {
        return 'full'; // Ép bảng tràn hết chiều ngang màn hình
    }

    protected function getHeaderActions(): array
    {
        return [
            // Nút Import
            \Filament\Actions\ImportAction::make()
                ->importer(\App\Filament\Imports\EmailImporter::class)
                ->label('Import All Data')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->modalHeading('Upload Email Database')
                ->modalDescription(fn() => new HtmlString('
                    <div class="text-sm space-y-2">
                        <p class="font-medium text-gray-700">Supported formats: .csv, .xlsx, and .txt</p>
                        <div>
                            <a href="/templates/email_template.csv" class="text-primary-600 underline hover:text-primary-500">
                                Download CSV Template
                            </a>
                        </div>
                        <div>
                            <a href="/templates/email_template.txt" class="text-primary-600 underline hover:text-primary-500">
                                Download TXT Template
                            </a>
                        </div>
                    </div>
                '))
                // 🟢 CHỈ HIỆN CHO ADMIN
                ->visible(fn() => auth()->user()?->isAdmin()),

            // Nút Export
            \Filament\Actions\ExportAction::make()
                ->exporter(\App\Filament\Exports\EmailExporter::class)
                ->label('Export All Data')
                ->color('info')
                ->icon('heroicon-o-arrow-down-tray')
                // 🟢 CHỈ HIỆN CHO ADMIN
                ->visible(fn() => auth()->user()?->isAdmin()),

            //Nút Sync to Google Sheet
            Actions\Action::make('sync_to_google_sheet')
                ->label('Sync to Google Sheet')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                // 🟢 CHỈ HIỆN CHO ADMIN
                ->visible(fn() => auth()->user()?->isAdmin())
                ->action(function () {
                    $sheetService = app(GoogleSheetService::class);
                    $targetTab = 'Emails';

                    try {
                        // 1. Tự tạo Tab nếu chưa có
                        $sheetService->createSheetIfNotExist($targetTab);

                        // 2. Lấy dữ liệu (Kèm eager loading để tránh chạy chậm)
                        $records = EmailResource::getEloquentQuery()
                            ->withCount('accounts')
                            ->with('accounts')
                            ->get();

                        if ($records->isEmpty()) {
                            Notification::make()->title('No data to sync')->warning()->send();
                            return;
                        }

                        // 3. Tạo mảng dữ liệu với Header
                        $rows = [EmailResource::$emailHeaders];
                        foreach ($records as $record) {
                            $rows[] = EmailResource::formatEmailForSheet($record);
                        }

                        // 4. Đẩy lên Sheet (11 cột từ A đến K)
                        $sheetService->updateSheet($rows, 'A1:K', $targetTab);

                        // 🟢 5. TỰ ĐỘNG TÔ MÀU (Giúp nhân viên nhìn nhanh trạng thái)
                        $statusIdx = array_search('Status', EmailResource::$emailHeaders);
                        $sheetService->applyFormattingWithRules($targetTab, $statusIdx, [
                            'Live'     => ['red' => 0.85, 'green' => 0.95, 'blue' => 0.85], // Xanh lá
                            'Disabled' => ['red' => 1.0,  'green' => 0.8,  'blue' => 0.8],  // Đỏ nhạt
                            'Locked'   => ['red' => 0.9,  'green' => 0.4,  'blue' => 0.4],  // Đỏ đậm
                        ]);

                        // 🟢 6. THU GỌN CỘT (Giúp bảng tính gọn gàng)
                        $sheetService->formatColumnsAsClip($targetTab, 2, 3);   // Cột Email & Pass
                        $sheetService->formatColumnsAsClip($targetTab, 9, 10);  // Cột Platforms & Note

                        Notification::make()
                            ->title('Success!')
                            ->body("All Emails has been synced and formatted.")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Sync Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            // Nút Create
            \Filament\Actions\CreateAction::make()
            ->visible(fn() => auth()->user()?->isAdmin()),
        ];
    }
}
