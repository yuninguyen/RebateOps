<?php

namespace App\Filament\Resources\PayPalResource\Pages;

use App\Filament\Resources\PayPalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPayPal extends EditRecord
{
    protected static string $resource = PayPalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
