<?php

namespace App\Filament\Resources\RetailMeNotResource\Pages;

use App\Filament\Resources\RetailMeNotResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Services\GoogleSyncService;

class ListRetailMeNots extends ListRecords
{
    protected static string $resource = RetailMeNotResource::class;
    use \App\Filament\Traits\HasSyncToSheetAction;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSyncToSheetAction('syncAccounts', 'Accounts'),
            Actions\CreateAction::make(),
        ];
    }
}
