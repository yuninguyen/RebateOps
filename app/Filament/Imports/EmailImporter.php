<?php

namespace App\Filament\Imports;

use App\Models\Email;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\FileUpload;
use OpenSpout\Common\Entity\Row;

class EmailImporter extends Importer
{
    protected static ?string $model = Email::class;

    public static function getColumns(): array
    {
        return [
            // Map cột Email Address trong file Excel vào trường 'email' của model Email
            ImportColumn::make('email')
                ->label('Email Address')
                ->requiredMapping()
                ->rules(['required', 'email'])
                //Loại bỏ khoảng trắng thừa để dữ liệu luôn chuẩn
                ->castStateUsing(fn ($state) => strtolower(trim($state))),

            // Map cột Email Password trong file Excel vào trường 'email_password' của model Email
            ImportColumn::make('email_password')
                ->label('Email Password')
                ->rules(['required']),

            // Map cột Recovery Email trong file Excel vào trường 'recovery_email' của model Email
            ImportColumn::make('recovery_email')
                ->label('Recovery Email')
                ->rules(['nullable', 'email']),

            ImportColumn::make('two_factor_code')
                ->label('2FA Code')
                ->rules(['nullable', 'string']),

            ImportColumn::make('email_created_at')
            ->label('Date Create')
            ->rules(['nullable', 'string']),

            // Map cột Note trong file Excel vào trường 'note' của model Email
            ImportColumn::make('note')
                ->label('Note')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Email
    {
        $record = Email::firstOrNew([
            'email' => $this->data['email'], // Sử dụng email làm điều kiện để tìm kiếm hoặc tạo mới
        ]);

        // Tự động nhận diện Provider
        if (isset($this->data['email'])) {
            $domain = substr(strrchr($this->data['email'], "@"), 1); // Lấy phần sau dấu @ của email
            $record->provider = strtolower(explode('.', $domain)[0]); // Lấy phần trước dấu chấm của domain để làm provider
        }

        // Gán trạng thái mặc định là Live khi import mới
        if (! $record->exists) {
            $record->status = 'active';
        }

        return $record;
    }


    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your email import has completed and ' . number_format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
