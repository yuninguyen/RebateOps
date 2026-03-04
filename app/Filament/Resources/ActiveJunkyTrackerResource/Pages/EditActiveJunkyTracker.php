<?php

namespace App\Filament\Resources\ActiveJunkyTrackerResource\Pages;

use App\Filament\Resources\ActiveJunkyTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class EditActiveJunkyTracker extends EditRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = ActiveJunkyTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
