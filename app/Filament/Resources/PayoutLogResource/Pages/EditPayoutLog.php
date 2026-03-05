<?php

namespace App\Filament\Resources\PayoutLogResource\Pages;

use App\Filament\Resources\PayoutLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Services\GoogleSheetService;

class EditPayoutLog extends EditRecord
{
    protected static string $resource = PayoutLogResource::class;

    protected function afterSave(): void
    {
        // Cứ lưu xong là "bộ não" ở Resource tự quét lại và đẩy lên Sheet
        // PayoutLogResource::syncToGoogleSheet();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
