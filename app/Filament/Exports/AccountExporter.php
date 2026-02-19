<?php

namespace App\Filament\Exports;

use App\Models\Account;
use App\Filament\Resources\AccountResource;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AccountExporter extends Exporter
{
    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('platform')->label('Platform'),
            ExportColumn::make('email.email')->label('Email Address'),
            ExportColumn::make('password') ->label('Password Platform'),

            ExportColumn::make('state')
                ->label('State')
                ->formatStateUsing(fn($state) => $state ?? 'N/A'), // Xuất trực tiếp CA, TX, NY...

            ExportColumn::make('device')
                ->label('Device Create')
                ->formatStateUsing(fn($state) => $state ?? 'N/A'),

            ExportColumn::make('account_created_at')
                ->label('Date Create')
                ->formatStateUsing(fn($state) => $state instanceof \Carbon\Carbon ? $state->format('d/m/Y') : 'N/A'), // Định dạng ngày tạo
            
            ExportColumn::make('paypal_info')
                ->label('Personal Information')
                ->formatStateUsing(fn($state) => $state ?? 'N/A'),

            ExportColumn::make('device_linked_paypal')
                ->label('Device Linked')
                ->formatStateUsing(fn($state) => $state ?? 'N/A'),

            ExportColumn::make('paypal_linked_at')
                ->label('Date Linked PayPal')
                ->formatStateUsing(fn($state) => $state instanceof \Carbon\Carbon ? $state->format('d/m/Y') : 'N/A'), // Định dạng ngày cập nhật
            
            ExportColumn::make('status')
                ->label('Platform Status')
                ->formatStateUsing(function ($state) {
                    $statusLabels = [
                        'active' => 'Active',
                        'used' => 'In Use',
                        'banned' => 'Banned',
                        'limited' => 'PayPal Limited',
                        'linked' => 'Linked PayPal',
                        'unlinked' => 'Unlinked PayPal',
                        'not_linked' => 'Chưa link PayPal',
                        'no_paypal_needed' => 'Không cần link PayPal',
                    ];
                    // Chuyển mảng status (ví dụ: used,linked) thành chuỗi (In Use, Linked PayPal)
                    $statuses = is_array($state) ? $state : explode(',', $state);
                    return collect($statuses)
                        ->map(fn($s) => $statusLabels[trim($s)] ?? ucfirst($s))
                        ->join(', ');
                }),

            ExportColumn::make('note')->label('Platform Note')
                ->formatStateUsing(fn($state) => $state ?? 'N/A'),

            ExportColumn::make('user.name')
                ->label('Holder')
                ->formatStateUsing(fn($state) => $state ?? 'N/A'), // Hiện tên người giữ tài khoản

        ];
    }

    public function getFileName(Export $export): string
    {
        // Lấy bản ghi đầu tiên trong danh sách đang export để kiểm tra Platform
        $firstRecord = $export->query()->first();
        $platformName = $firstRecord?->platform ?? 'General';

        // Chuyển tên Platform thành dạng chữ đẹp (ví dụ: Rakuten, Active_Junky)
        $formattedPlatform = str($platformName)->replace(' ', '_')->ucfirst();
        // Định dạng: Account_List_15_02_2026.xlsx
        return "Export_{$export->getKey()}_{$formattedPlatform}_Account_List_" . now()->format('d_m_Y');
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your account export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
