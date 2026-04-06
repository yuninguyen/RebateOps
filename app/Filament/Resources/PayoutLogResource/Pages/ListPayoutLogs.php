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
    use \App\Filament\Traits\HasSyncToSheetAction;

    protected static string $resource = PayoutLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSyncToSheetAction('syncPayoutLogs', 'Payout Logs'),
            Actions\CreateAction::make(),
        ];
    }
}
