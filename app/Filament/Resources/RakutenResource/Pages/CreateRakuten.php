<?php

namespace App\Filament\Resources\RakutenResource\Pages;

use App\Filament\Resources\RakutenResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateRakuten extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.

    protected static string $resource = RakutenResource::class;
}
