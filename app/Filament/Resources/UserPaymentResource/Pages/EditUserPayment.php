<?php

namespace App\Filament\Resources\UserPaymentResource\Pages;

use App\Filament\Resources\UserPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class EditUserPayment extends EditRecord
{

    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.

    protected static string $resource = UserPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
