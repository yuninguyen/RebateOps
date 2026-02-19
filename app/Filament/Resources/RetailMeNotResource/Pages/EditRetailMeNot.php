<?php

namespace App\Filament\Resources\RetailMeNotResource\Pages;

use App\Filament\Resources\RetailMeNotResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRetailMeNot extends EditRecord
{
    protected static string $resource = RetailMeNotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
