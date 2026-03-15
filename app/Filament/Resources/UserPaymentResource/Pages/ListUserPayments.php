<?php

namespace App\Filament\Resources\UserPaymentResource\Pages;

use App\Filament\Resources\UserPaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserPayments extends ListRecords
{
    protected static string $resource = UserPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
