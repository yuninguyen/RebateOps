<?php

namespace App\Filament\Resources\PayoutLogResource\Pages;

use App\Filament\Resources\PayoutLogResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayoutLog extends CreateRecord
{
    protected static string $resource = PayoutLogResource::class;

    // 🟢 TỰ ĐỘNG SYNC SAU KHI TẠO MỚI THÀNH CÔNG
    protected function afterSave(): void
    {
        // Gọi hàm "bộ não" từ Resource
        //PayoutLogResource::syncToGoogleSheet();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
