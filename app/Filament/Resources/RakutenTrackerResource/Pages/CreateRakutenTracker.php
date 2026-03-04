<?php

namespace App\Filament\Resources\RakutenTrackerResource\Pages;

use App\Filament\Resources\RakutenTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateRakutenTracker extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = RakutenTrackerResource::class;
}
