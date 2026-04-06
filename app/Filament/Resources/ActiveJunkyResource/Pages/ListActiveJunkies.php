<?php

namespace App\Filament\Resources\ActiveJunkyResource\Pages;

use App\Filament\Resources\ActiveJunkyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListActiveJunkies extends ListRecords
{
    protected static string $resource = ActiveJunkyResource::class;
    use \App\Filament\Traits\HasSyncToSheetAction;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSyncToSheetAction('syncAccounts', 'Accounts'),
            Actions\CreateAction::make(),
        ];
    }
}
