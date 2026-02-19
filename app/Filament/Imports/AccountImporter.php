<?php

namespace App\Filament\Imports;

use App\Models\Account;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class AccountImporter extends Importer
{
    protected static ?string $model = Account::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('platform'),
            ImportColumn::make('email')
                ->requiredMapping()
                ->relationship() // Tự động tìm/tạo email trong bảng liên quan
                ->castStateUsing(fn($state) => strtolower(trim($state))), //Loại bỏ khoảng trắng thừa để dữ liệu luôn chuẩn
            
            
            ImportColumn::make('password')
                ->label('Password Platform')
                ->rules(['required']),           
            
            ImportColumn::make('status')
                -> label('Status')
                ->rules(['nullable', 'string'])
                ->castStateUsing(fn($state) => explode(',', $state)), // Chuyển "active,linked" thành mảng
            

            ImportColumn::make('note')
                ->label('Note')
                ->rules(['nullable', 'string']),
            
                
            ImportColumn::make('user_id')                
                ->label('Holder')
                ->rules(['nullable', 'string']),

            ImportColumn::make('account_created_at')
            ->label('Holder')
            ->rules(['nullable', 'date']),

            ImportColumn::make('paypal_linked_at')
            ->label('Holder')
            ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?Account
    {
        // return Account::firstOrNew([
        //     // Update existing records, matching them by `$this->data['column_name']`
        //     'email' => $this->data['email'],
        // ]);

        return new Account();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your account import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
