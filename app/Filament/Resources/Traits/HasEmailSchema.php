<?php

namespace App\Filament\Resources\Traits;

trait HasEmailSchema
{

    // 1. Khai báo Header 11 cột
    public static array $emailHeaders = [
        'ID',
        'Status',
        'Email Address',
        'Password',
        'Recovery Email',
        '2FA Code',
        'Date Created',
        'Provider',
        'Usage',
        'Platforms',
        'Note'
    ];

    // 2. Định dạng dữ liệu khớp 100% với Database của bạn
    public static function formatEmailForSheet($record): array
    {
        $data = [
            (string) $record->id,
            (string) match ($record->status) {
                'active' => 'Live',
                'disabled' => 'Disabled',
                'locked' => 'Locked',
                default => 'N/A',
            },
            (string) $record->email,
            (string) $record->email_password,
            (string) $record->recovery_email ?? 'N/A',
            (string) $record->two_factor_code ?? 'N/A',
            (string) ($record->email_created_at ? $record->email_created_at->format('d/m/Y') : 'N/A'),
            (string) ucfirst($record->provider ?? 'Other'),
            (string) ($record->accounts_count ?? 0) . ' Account(s)',
            (string) ($record->accounts->pluck('platform')->implode(', ') ?: 'N/A'),
            (string) $record->note ?? '',
        ];
        return $data;
    }

    public static function syncToGoogleSheet(): void
    {
        $sheetService = app(\App\Services\GoogleSheetService::class);
        $targetTab = 'Emails';

        try {
            $sheetService->createSheetIfNotExist($targetTab);
            // 🟢 Tối ưu: Lấy kèm count và relation để chạy cực nhanh
            $records = \App\Models\Email::withCount('accounts')->with('accounts')->get();
            $rows = [static::$emailHeaders];

            foreach ($records as $record) {
                $rows[] = static::formatEmailForSheet($record);
            }

            $sheetService->updateSheet($rows, 'A1:K', $targetTab);

            // ĐỊNH DẠNG MÀU SẮC: Die -> Đỏ, Live -> Xanh
            // 3. ĐỊNH DẠNG MÀU SẮC
            $statusIdx = array_search('Status', static::$emailHeaders);
            $sheetService->applyFormattingWithRules($targetTab, $statusIdx, [
                'Live'     => ['red' => 0.85, 'green' => 0.95, 'blue' => 0.85], // Xanh
                'Disabled' => ['red' => 1.0,  'green' => 0.8,  'blue' => 0.8],  // Đỏ nhạt
                'Locked'   => ['red' => 0.9,  'green' => 0.4,  'blue' => 0.4],  // Đỏ đậm
            ]);

            // Format các cột dài cho gọn
            $sheetService->formatColumnsAsClip($targetTab, 2, 3); // Email & Pass
            $sheetService->formatColumnsAsClip($targetTab, 9, 10); // Platforms & Note

            \Filament\Notifications\Notification::make()->title('Email Synced!')->success()->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
        }
    }
}
