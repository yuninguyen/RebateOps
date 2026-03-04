<?php

namespace App\Filament\Resources\RetailMeNotResource\Pages;

use App\Filament\Resources\RetailMeNotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class EditRetailMeNot extends EditRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = RetailMeNotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
