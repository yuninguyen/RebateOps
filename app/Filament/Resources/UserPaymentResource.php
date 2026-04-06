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
    // protected static ?string $navigationGroup = 'Wallet & Payouts'; // Filament sẽ lấy text này so khớp với AdminPanelProvider đã localize
    // protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'wallet_payout';
    }
    public static function getNavigationSort(): int
    {
        return 3;
    }

    public static function getNavigationLabel(): string
    {
        return __('system.labels.revenue_split');
    }

    public static function getModelLabel(): string
    {
        return __('system.labels.revenue_split');
    }

    public static function getPluralModelLabel(): string
    {
        return __('system.labels.revenue_split_list');
    }

    // 🟢 1. PHÂN QUYỀN DỮ LIỆU: Staff chỉ thấy lương của mình
    public static function getEloquentQuery(): Builder
    {
        // 🟢 Sắp xếp: Pending (desc) lên trên, sau đó mới đến Paid. Tiếp theo là Created At (desc)
        $query = parent::getEloquentQuery()
            ->addSelect([
                '*',
                \Illuminate\Support\Facades\DB::raw("CASE WHEN transaction_type LIKE 'Gift Card%' THEN 'gift_card' ELSE 'paypal' END as asset_group")
            ])
            ->orderBy('status', 'desc')
            ->orderBy('created_at', 'desc');

        // Nếu không phải Admin và không phải Finance, ép query chỉ tìm user_id của chính người đó
        if (!auth()->user()?->isAdmin() && !auth()->user()?->isFinance()) {
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
        return auth()->user()?->isAdmin() || auth()->user()?->isFinance(); // Admin & Finance được sửa (để up bill)
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->isAdmin() || auth()->user()?->isFinance();
    }

    // 🟢 3. FORM GIAO DIỆN (Dành cho Admin Up Bill)
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make(__('system.labels.transaction_details'))
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label(__('system.labels.status'))
                            ->options([
                                'pending' => '⏳ ' . __('system.status.pending'),
                                'paid' => '✅ ' . __('system.status.completed'),
                            ])
                            ->required()
                            ->native(false),

                        Forms\Components\FileUpload::make('payment_proof')
                            ->label(__('system.user_payments.fields.payment_proof'))
                            ->image()
                            ->directory('payment-proofs')
                            ->openable()
                            ->downloadable(),

                        Forms\Components\Textarea::make('note')
                            ->label(__('system.labels.note'))
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
                    ->label(__('system.labels.user'))
                    ->alignment(Alignment::Center)
                    ->searchable()
                    ->visible(fn() => auth()->user()?->isAdmin() || auth()->user()?->isFinance()),

                Tables\Columns\TextColumn::make('platform')
                    ->label(__('system.labels.platform'))
                    ->alignment(Alignment::Center)
                    ->formatStateUsing(function ($state) {
                        return \App\Models\Platform::where('slug', $state)->value('name') 
                            ?? ucwords(str_replace(['_', '-'], ' ', $state ?? ''));
                    }),

                Tables\Columns\TextColumn::make('transaction_type')
                    ->label(__('system.labels.description'))
                    ->formatStateUsing(fn($state) => str_replace(['(', ')'], ['- ', ''], $state))
                    ->description(function ($record) {
                        if (auth()->user()?->isAdmin() || auth()->user()?->isFinance()) {
                            return __('system.payout_logs.fields.market_rate') . ': ' . number_format($record->exchange_rate) . ' | ' . __('system.payout_logs.fields.payout_rate') . ': ' . number_format($record->payout_rate) . ' (' . ($record->payout_percentage ?? 100) . '%)';
                        }
                        return __('system.labels.exchange_rate') . ': ' . number_format($record->payout_rate);
                    })
                    ->searchable()
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('total_usd')
                    ->label(__('system.labels.rebate_amount_usd'))
                    ->money('USD') // Tự format có chữ $
                    ->color('success')
                    ->alignment(Alignment::Center)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->money('USD')),

                Tables\Columns\TextColumn::make('total_vnd')
                    ->label(__('system.labels.total_vnd'))
                    ->money('VND', locale: 'vi_VN') // Tự format 25.000.000 ₫
                    ->color('primary')
                    ->weight('bold')
                    ->alignment(Alignment::Center)
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')->money('VND', locale: 'vi_VN')),


                Tables\Columns\TextColumn::make('status')
                    ->label(__('system.labels.status'))
                    ->alignment(Alignment::Center)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => __('system.status.pending'),
                        'paid' => __('system.status.completed'),
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('system.labels.settlement_date'))
                    ->dateTime('d/m/Y H:i')
                    ->alignment(Alignment::Center),
            ])
            ->groups([
                Tables\Grouping\Group::make('asset_group')
                    ->label(__('system.labels.revenue_split'))
                    ->getTitleFromRecordUsing(function ($record) {
                        return $record->asset_group === 'gift_card' ? '🎁 Gift Card' : '💰 PayPal';
                    })
            ])
            ->defaultGroup('asset_group')
            ->filters([
                // 1. Lọc theo Platform (Sàn) — CHỈ HIỆN SÀN CÓ DỮ LIỆU THỰC TẾ
                Tables\Filters\SelectFilter::make('platform')
                    ->label(__('system.labels.platform'))
                    ->options(fn() => UserPayment::query()->distinct()->whereNotNull('platform')->pluck('platform', 'platform'))
                    ->multiple(),

                // 2. Lọc theo User (Nhân viên) — CHỈ HIỆN USER CÓ DỮ LIỆU THỰC TẾ
                Tables\Filters\SelectFilter::make('user_id')
                    ->label(__('system.labels.user'))
                    ->options(fn() => User::whereIn('id', UserPayment::query()->distinct()->pluck('user_id'))->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->visible(fn() => auth()->user()?->isAdmin() || auth()->user()?->isFinance()),

                // 3. Lọc theo Trạng thái
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('system.labels.status'))
                    ->options([
                        'pending' => __('system.status.pending'),
                        'paid' => __('system.status.completed'),
                    ]),

                // 4. Lọc TỪ NGÀY
                Tables\Filters\Filter::make('created_from')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label(__('system.labels.settlement_date') . ' - ' . __('system.labels.from')),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when($data['from'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)))
                    ->indicateUsing(fn(array $data): array => ($data['from'] ?? null) ? ['From ' . \Carbon\Carbon::parse($data['from'])->format('d/m/Y')] : []),

                // 5. Lọc ĐẾN NGÀY
                Tables\Filters\Filter::make('created_until')
                    ->form([
                        Forms\Components\DatePicker::make('until')
                            ->label(__('system.labels.settlement_date') . ' - ' . __('system.labels.until')),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query->when($data['until'], fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(fn(array $data): array => ($data['until'] ?? null) ? ['Until ' . \Carbon\Carbon::parse($data['until'])->format('d/m/Y')] : []),
            ])
            ->filtersFormColumns(auth()->user()?->isAdmin() || auth()->user()?->isFinance() ? 5 : 4)
            ->filtersLayout(FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label(fn() => (auth()->user()?->isAdmin() || auth()->user()?->isFinance()) ? __('system.user_payments.actions.up_bill') : __('system.user_payments.actions.view_bill')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->isAdmin() || auth()->user()?->isFinance()),
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
