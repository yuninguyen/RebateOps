<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserPaymentResource\Pages;
use App\Filament\Resources\UserPaymentResource\RelationManagers;
use App\Models\UserPayment;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\Traits\HasPlatform;
use Filament\Tables\Enums\FiltersLayout;


class UserPaymentResource extends Resource
{
    protected static ?string $model = UserPayment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'WALLET & PAYOUTS';
    protected static ?int $navigationSort = 3;

    public static function getNavigationLabel(): string
    {
        return __('system.revenue_split');
    }

    public static function getModelLabel(): string
    {
        return __('system.revenue_split');
    }

    public static function getPluralModelLabel(): string
    {
        return __('system.revenue_split_list');
    }

    // 🟢 1. PHÂN QUYỀN DỮ LIỆU: Staff chỉ thấy lương của mình
    public static function getEloquentQuery(): Builder
    {
        // 🟢 Sắp xếp: Pending (desc) lên trên, sau đó mới đến Paid. Tiếp theo là Created At (desc)
        $query = parent::getEloquentQuery()
            ->orderBy('status', 'desc')
            ->orderBy('created_at', 'desc');

        // Nếu không phải Admin, ép query chỉ tìm user_id của chính người đó
        if (!auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    // 🟢 2. KHÓA CHẶT QUYỀN: Staff không được sửa/xóa/tạo
    public static function canCreate(): bool
    {
        return false; // Không ai được tạo tay (Tạo tự động từ lúc chốt sổ rồi)
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->isAdmin(); // Chỉ Admin được sửa (để up bill)
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin();
    }

    // 🟢 3. FORM GIAO DIỆN (Dành cho Admin Up Bill)
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment information')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => '⏳ Pending',
                                'paid' => '✅ Paid',
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\FileUpload::make('payment_proof')
                            ->label('Payment Proof')
                            ->image()
                            ->directory('payment-proofs')
                            ->openable() // Cho phép bấm vào để xem ảnh to
                            ->downloadable(),

                        Forms\Components\Textarea::make('note')
                            ->label('Note')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    // 🟢 4. BẢNG HIỂN THỊ (Giao diện xem tiền)
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Cột Tên Staff (Tự động ẩn đi nếu Staff đang xem để đỡ chật màn hình)
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->visible(fn() => auth()->user()?->isAdmin()),

                Tables\Columns\TextColumn::make('platform')
                    ->label('Platform')
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label('Transaction Type')
                    ->description(fn($record) => 'Market: ' . number_format($record->exchange_rate) . ' | Payout: ' . number_format($record->payout_rate) . ' (' . ($record->payout_percentage ?? 100) . '%)')
                    ->searchable()
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('total_usd')
                    ->label('Total USD')
                    ->money('USD') // Tự format có chữ $
                    ->color('success')
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('total_vnd')
                    ->label('Total (VND)')
                    ->money('VND', locale: 'vi_VN') // Tự format 25.000.000 ₫
                    ->color('primary')
                    ->weight('bold')
                    ->alignment(Alignment::Center),

                // 🟢 THÊM CỘT LỢI NHUẬN (CHỈ ADMIN THẤY)
                Tables\Columns\TextColumn::make('profit_vnd')
                    ->label('Profit')
                    ->money('VND', locale: 'vi_VN')
                    ->color('success')
                    ->visible(fn() => auth()->user()?->isAdmin())
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->alignment(Alignment::Center),
            ])
            ->groups([
                Tables\Grouping\Group::make('transaction_type')
                    ->label('Payment Type')
                    ->getTitleFromRecordUsing(function ($record) {
                        return str_contains($record->transaction_type, 'Gift Card') ? '🎁 Gift Card' : '💰 PayPal';
                    })
            ])
            ->defaultGroup('transaction_type')
            ->filters([
                // 1. Lọc theo Platform (Sàn) — CHỈ HIỆN SÀN CÓ DỮ LIỆU THỰC TẾ
                Tables\Filters\SelectFilter::make('platform')
                    ->label('Platform')
                    ->options(fn() => UserPayment::query()->distinct()->whereNotNull('platform')->pluck('platform', 'platform'))
                    ->multiple(),

                // 2. Lọc theo User (Nhân viên) — CHỈ HIỆN USER CÓ DỮ LIỆU THỰC TẾ
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('User')
                    ->options(fn() => User::whereIn('id', UserPayment::query()->distinct()->pluck('user_id'))->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()?->isAdmin()),

                // 3. Lọc theo Trạng thái
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ]),

                // 4. Lọc TỪ NGÀY
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('From Date'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when($data['from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)))
                    ->indicateUsing(fn(array $data): array => ($data['from'] ?? null) ? ['From ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y')] : []),

                // 5. Lọc ĐẾN NGÀY
                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('until')
                            ->label('To Date'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when($data['until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(fn(array $data): array => ($data['until'] ?? null) ? ['Until ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y')] : []),
            ])
            ->filtersFormColumns(auth()->user()?->isAdmin() ? 5 : 4)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn() => auth()->user()?->isAdmin() ? 'Up Bill' : 'View Bill'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->isAdmin()),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserPayments::route('/'),
            //'create' => Pages\CreateUserPayment::route('/create'), // Bỏ trang Create đi vì mình không tạo bằng tay
            'edit' => Pages\EditUserPayment::route('/{record}/edit'),
        ];
    }
}
