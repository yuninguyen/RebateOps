<?php

namespace App\Filament\Resources\RetailMeNotTrackerResource\Pages;

use App\Filament\Resources\RetailMeNotTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class EditRetailMeNotTracker extends EditRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.

    protected static string $resource = RetailMeNotTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
