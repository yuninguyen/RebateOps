<?php

namespace App\Filament\Resources\RetailMeNotResource\Pages;

use App\Filament\Resources\RetailMeNotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRetailMeNots extends ListRecords
{
    protected static string $resource = RetailMeNotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
