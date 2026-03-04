<?php

namespace App\Filament\Resources\RebateTrackerResource\Pages;

use App\Filament\Resources\RebateTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRebateTracker extends CreateRecord
{
    
    protected static string $resource = RebateTrackerResource::class;

// Thêm các nút hỗ trợ nhập nhanh
    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            $this->getCreateAnotherFormAction(), // Nút "Tạo & tạo tiếp"
            $this->getCancelFormAction(),
        ];
    }

    // Sau khi lưu xong (nút Create thường) thì quay về trang danh sách
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
