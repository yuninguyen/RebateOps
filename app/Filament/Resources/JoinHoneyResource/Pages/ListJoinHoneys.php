<?php

namespace App\Filament\Resources\JoinHoneyResource\Pages;

use App\Filament\Resources\JoinHoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJoinHoneys extends ListRecords
{
    protected static string $resource = JoinHoneyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
