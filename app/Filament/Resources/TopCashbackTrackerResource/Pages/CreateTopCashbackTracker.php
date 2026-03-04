<?php

namespace App\Filament\Resources\TopCashbackTrackerResource\Pages;

use App\Filament\Resources\TopCashbackTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateTopCashbackTracker extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = TopCashbackTrackerResource::class;
}
