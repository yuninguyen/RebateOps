<?php

namespace App\Filament\Resources\PriceTrackerResource\Pages;

use App\Filament\Resources\PriceTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class EditPriceTracker extends EditRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = PriceTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
