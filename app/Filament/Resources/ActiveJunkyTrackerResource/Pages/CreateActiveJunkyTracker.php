<?php

namespace App\Filament\Resources\ActiveJunkyTrackerResource\Pages;

use App\Filament\Resources\ActiveJunkyTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateActiveJunkyTracker extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = ActiveJunkyTrackerResource::class;
}
