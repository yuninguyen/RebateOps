<?php

namespace App\Filament\Resources\RakutenResource\Pages;

use App\Filament\Resources\RakutenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListRakutens extends ListRecords
{
    protected static string $resource = RakutenResource::class;

    // Thêm hàm này để bảng giãn rộng ra toàn màn hình
    public function getMaxWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
