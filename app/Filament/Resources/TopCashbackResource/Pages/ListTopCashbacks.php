<?php

namespace App\Filament\Resources\TopCashbackResource\Pages;

use App\Filament\Resources\TopCashbackResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTopCashbacks extends ListRecords
{
    protected static string $resource = TopCashbackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
