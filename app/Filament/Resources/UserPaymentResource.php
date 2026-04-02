<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserPaymentResource\Pages;
use App\Filament\Resources\UserPaymentResource\RelationManagers;
use App\Models\UserPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Model;


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
        $query = parent::getEloquentQuery()->latest(); // Xếp phiếu mới nhất lên đầu

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
                    ->description(fn($record) => 'Market Rate: ' . number_format($record->exchange_rate) . ' | Payout: ' . number_format($record->payout_rate)) // Hiện cả 2 Rate
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
                // Bộ lọc trạng thái
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ]),
            ])
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
