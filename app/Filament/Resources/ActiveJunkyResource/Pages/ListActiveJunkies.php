<?php

namespace App\Filament\Resources\ActiveJunkyResource\Pages;

use App\Filament\Resources\ActiveJunkyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActiveJunkies extends ListRecords
{
    protected static string $resource = ActiveJunkyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
