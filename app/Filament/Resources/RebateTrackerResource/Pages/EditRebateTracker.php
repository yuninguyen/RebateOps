<?php

namespace App\Filament\Resources\RebateTrackerResource\Pages;

use App\Filament\Resources\RebateTrackerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Filament\Resources\Traits\RedirectsToIndex; // <-- Nhúng Trait

class EditRebateTracker extends EditRecord
{
    use RedirectsToIndex; // <-- Gọi ra sử dụng, xong! Không cần viết lại hàm getRedirectUrl nữa.
    
    protected static string $resource = RebateTrackerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Nút Thêm mới cho chính Account này
            Actions\Action::make('add_another')
                ->label('Add another order for this account')
                ->icon('heroicon-m-arrow-path')
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Create a copy for the new order?')
                ->modalDescription('The system will create a new record with the same Account, User, and Device for you to continue entering the information.')
                ->action(function ($record) {
                    // Nhân bản bản ghi hiện tại
                    $newOrder = $record->replicate()->fill([
                        'store_name' => '', // Xóa tên store cũ để nhập mới
                        'order_id' => '',   // Xóa ID cũ
                        'order_value' => 0,
                        'rebate_amount' => 0,
                        'status' => 'clicked',
                    ]);

                    $newOrder->save();

                    // Chuyển hướng ngay lập tức đến trang Edit của đơn mới vừa tạo
                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $newOrder]));
                }),

            Actions\DeleteAction::make(),
        ];
    }
}
