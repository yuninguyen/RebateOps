<?php

namespace App\Filament\Resources\PayoutMethodResource\Pages;

use App\Filament\Resources\PayoutMethodResource;
use App\Services\GoogleSheetService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListPayoutMethods extends ListRecords
{
    protected static string $resource = PayoutMethodResource::class;

    // Thêm dòng này để định danh rõ ràng, tránh trùng với PayoutLog
    protected static ?string $slug = 'payout-methods';

    protected function getHeaderActions(): array
    {
        return [
            // NÚT SYNC PAYMENT METHODS
            Actions\Action::make('sync_payout_methods_to_sheet')
                ->label('Sync to Google Sheet')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->action(fn() => \App\Filament\Resources\PayoutMethodResource::syncToGoogleSheet()),

            // Nút Kéo dữ liệu về
            \Filament\Actions\Action::make('sync_from_sheet')
                ->label('Sync From Google Sheet')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('warning')
                ->action(fn() => \App\Filament\Resources\PayoutMethodResource::syncFromGoogleSheet()),
                
            // Nút Create
            Actions\CreateAction::make(),
        ];
    }
}
