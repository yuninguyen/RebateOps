<?php

namespace App\Filament\Resources\RakutenResource\Pages;

use App\Filament\Resources\RakutenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRakuten extends EditRecord
{
    protected static string $resource = RakutenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
