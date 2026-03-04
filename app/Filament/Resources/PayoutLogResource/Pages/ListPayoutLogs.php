<?php

namespace App\Filament\Resources\PayoutLogResource\Pages;

use App\Filament\Resources\PayoutLogResource;
use App\Services\GoogleSheetService; // Import Service của bạn
use App\Models\PayoutLog;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayoutLogs extends ListRecords
{
    protected static string $resource = PayoutLogResource::class;

    // 1. Thêm nút bấm vào giao diện
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('sync_to_sheet')
                ->label('Sync to Google Sheet')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn() => PayoutLogResource::syncToGoogleSheet()),

            // Nút Sync ngược từ Sheet về
            Actions\Action::make('sync_from_sheet')
                ->label('Sync From Google Sheet')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(fn() => PayoutLogResource::syncFromGoogleSheet()),
        ];
    }
}
