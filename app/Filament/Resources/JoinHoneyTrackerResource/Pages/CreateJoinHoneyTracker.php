<?php

namespace App\Filament\Resources\JoinHoneyTrackerResource\Pages;

use App\Filament\Resources\JoinHoneyTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateJoinHoneyTracker extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = JoinHoneyTrackerResource::class;
}
