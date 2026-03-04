<?php

namespace App\Filament\Resources\PriceTrackerResource\Pages;

use App\Filament\Resources\PriceTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreatePriceTracker extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = PriceTrackerResource::class;
}
