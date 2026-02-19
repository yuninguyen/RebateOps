<?php

namespace App\Filament\Resources\EmailResource\Pages;

use App\Filament\Resources\EmailResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmail extends EditRecord
{
    protected static string $resource = EmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // CHÈN ĐOẠN NÀY ĐỂ QUAY VỀ DANH SÁCH SAU KHI LƯU
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
