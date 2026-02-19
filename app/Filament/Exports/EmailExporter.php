<?php

namespace App\Filament\Exports;

use App\Models\Email;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class EmailExporter extends Exporter
{
    protected static ?string $model = Email::class;

    public static function getColumns(): array
    {
        return [
            // Cột Status: Hiển thị "N/A" nếu status trống, và viết hoa chữ cái đầu nếu có giá trị
            ExportColumn::make('status')
                ->label('Status')
                ->state(function (Email $record): string {
                    $emailstatusLabels = [
                        'active' => 'Live',
                        'disabled' => 'Disabled',
                        'locked' => 'Locked',
                    ];

                    $currentemailStatuses = trim((string)$record->status);
                    return $emailstatusLabels[$currentemailStatuses] ?? ucfirst($currentemailStatuses ?: 'N/A');
                }),

            // Các cột còn lại giữ nguyên
            ExportColumn::make('email_created_at')
            ->label('Date Create')
            ->formatStateUsing(fn($state) => $state ? $state->format('d/m/Y') : 'N/A'),  

            ExportColumn::make('email')->label('Email Address'),
            ExportColumn::make('email_password')->label('Email Password'),
            ExportColumn::make('recovery_email')->label('Recovery Email'),

            // 2FA Code: Hiển thị N/A nếu trống
            ExportColumn::make('two_factor_code')
                ->label('2FA Code')
                ->state(fn(Email $record): string => $record->two_factor_code ?: 'N/A'),

            ExportColumn::make('note')
                ->label('Note')
                ->state(fn(Email $record): string => $record->note ?: 'N/A'),

            // Provider: Viết hoa chữ cái đầu (gmail -> Gmail)
            ExportColumn::make('provider')
                ->state(fn(Email $record): string => $record->provider ? ucfirst($record->provider) : 'Other'),

            // Thêm cột Account Count: Đếm số lượng Account liên quan đến mỗi Email
            ExportColumn::make('usage')
                ->label('Usage')
                // Sử dụng count() trên quan hệ accounts để đếm số lượng Account liên quan đến mỗi Email
                ->state(fn(Email $record): int => $record->accounts()->count() ?? 0),

            // Platforms: Liệt kê tên các Platform từ quan hệ
            ExportColumn::make('platforms')
                ->label('Platforms')
                ->state(function (Email $record): string {
                    $platforms = $record->accounts->pluck('platform')
                        ->map(fn($s) => ucfirst($s))
                        ->unique() // Tránh lặp tên nếu một email lặp platform
                        ->join(', ');

                    return $platforms ?: 'N/A';
                }),
        ];
    }

    public function getFileName(Export $export): string
    {
        // Định dạng tên file: Export_ID_Email_List_Ngày.xlsx
        return "Exported_{$export->getKey()}_Email_List_" . now()->format('d_m_Y');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your email export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
