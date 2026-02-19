<?php

namespace App\Filament\Resources\TopCashbackResource\Pages;

use App\Filament\Resources\TopCashbackResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTopCashback extends EditRecord
{
    protected static string $resource = TopCashbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
