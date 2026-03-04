<?php

namespace App\Filament\Resources\ActiveJunkyResource\Pages;

use App\Filament\Resources\ActiveJunkyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateActiveJunky extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = ActiveJunkyResource::class;
}
