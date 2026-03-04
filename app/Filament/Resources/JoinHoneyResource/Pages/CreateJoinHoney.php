<?php

namespace App\Filament\Resources\JoinHoneyResource\Pages;

use App\Filament\Resources\JoinHoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class CreateJoinHoney extends CreateRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.

    protected static string $resource = JoinHoneyResource::class;
}
