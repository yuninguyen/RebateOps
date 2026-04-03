<?php

namespace App\Filament\Resources\RebateTrackerResource\Pages;

use App\Filament\Resources\RebateTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRebateTrackers extends ListRecords
{
    protected static string $resource = RebateTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 2. NÚT TẠO MỚI MẶC ĐỊNH CỦA FILAMENT (MÀU CAM)
            Actions\CreateAction::make(),
        ];
    }
}
