<?php

namespace App\Filament\Resources\Traits;

trait RedirectsToIndex
{
    protected function getRedirectUrl(): string
    {
        // Tự động điều hướng về trang danh sách (index) của Resource hiện tại
        return $this->getResource()::getUrl('index');
    }
}