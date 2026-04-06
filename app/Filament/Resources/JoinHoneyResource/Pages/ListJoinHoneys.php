<?php

namespace App\Filament\Resources\JoinHoneyResource\Pages;

use App\Filament\Resources\JoinHoneyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListJoinHoneys extends ListRecords
{
    protected static string $resource = JoinHoneyResource::class;
    use \App\Filament\Traits\HasSyncToSheetAction;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSyncToSheetAction('syncAccounts', 'Accounts'),
            Actions\CreateAction::make(),
        ];
    }
}
