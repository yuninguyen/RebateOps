<?php

namespace App\Filament\Resources\TopCashbackResource\Pages;

use App\Filament\Resources\TopCashbackResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateTopCashback extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = TopCashbackResource::class;
}
