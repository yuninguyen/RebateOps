<?php

namespace App\Filament\Resources\PayoutMethodResource\Pages;

use App\Filament\Resources\PayoutMethodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayoutMethod extends EditRecord
{
    protected static string $resource = PayoutMethodResource::class;

    // --- THÊM HÀM NÀY ĐỂ REDIRECT VỀ TRANG LIST ---
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
