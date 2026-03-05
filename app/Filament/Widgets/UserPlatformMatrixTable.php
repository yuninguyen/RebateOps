<?php

namespace App\Filament\Widgets;

use App\Models\RebateTracker;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Filament\Support\Enums\Alignment;
// Import thêm các class phục vụ Filter
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;

class UserPlatformMatrixTable extends BaseWidget
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // Nếu là Admin -> Cho xem tất cả mọi thứ (Sử dụng logic từ Model User)
        if ($user && method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return $query;
        } // Nếu là Staff bình thường -> Chỉ cho xem Account
    }

    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Revenue Report by User & Platform';

    // Thêm vào trong class UserPlatformMatrixTable
    public function updatedTableFilters(): void
    {
        // Lấy ID user từ filter hiện tại của bảng
        $userId = $this->tableFilters['table_filter']['user_id'] ?? null;

        // Bắn tín hiệu kèm theo ID user ra toàn hệ thống Dashboard
        $this->dispatch('updateStatsUser', userId: $userId);
    }

    public function getTableRecordKey($record): string
    {
        return "{$record->user_name}-{$record->platform_name}";
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                RebateTracker::query()
                    ->join('accounts', 'rebate_trackers.account_id', '=', 'accounts.id')
                    ->join('users', 'rebate_trackers.user_id', '=', 'users.id')
                    ->select(
                        'users.name as user_name',
                        'accounts.platform as platform_name',
                        DB::raw('SUM(CASE WHEN rebate_trackers.status = "Clicked" THEN rebate_amount ELSE 0 END) as total_clicked'),
                        DB::raw('SUM(CASE WHEN rebate_trackers.status = "Missing" THEN rebate_amount ELSE 0 END) as total_missing'),
                        DB::raw('SUM(CASE WHEN rebate_trackers.status = "Pending" THEN rebate_amount ELSE 0 END) as total_pending'),
                        DB::raw('SUM(CASE WHEN rebate_trackers.status = "Confirmed" THEN rebate_amount ELSE 0 END) as total_confirmed'),
                        DB::raw('SUM(rebate_amount) as grand_total')
                    )
                    ->groupBy('users.name', 'accounts.platform')
            )
            // CẤU HÌNH BỘ LỌC NẰM NGANG
            ->filters([
                Filter::make('table_filter')
                    ->form([
                        Select::make('user_id')
                            ->label('Select User')
                            ->options(User::pluck('name', 'id'))
                            ->placeholder('Select User')
                            ->searchable()
                            ->live(),
                        DatePicker::make('from_date')
                            ->label('From Date'),
                        DatePicker::make('to_date')
                            ->label('To Date'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['user_id'],
                                fn(Builder $query, $userId): Builder => $query->where('rebate_trackers.user_id', $userId)
                            )
                            ->when(
                                $data['from_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('rebate_trackers.created_at', '>=', $date)
                            )
                            ->when(
                                $data['to_date'],
                                fn(Builder $query, $date): Builder => $query->whereDate('rebate_trackers.created_at', '<=', $date)
                            );
                    })
                    ->columns(3) // Chia 3 cột nằm ngang
                    ->columnSpanFull(),
            ], layout: Tables\Enums\FiltersLayout::AboveContent) // Ép hiển thị lên trên bảng

            ->defaultSort('user_name', 'asc')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('User')
                    ->weight('bold')
                    ->alignment(Alignment::Center),

                Tables\Columns\TextColumn::make('platform_name')
                    ->label('Platform')
                    ->alignment(Alignment::Center)
                    // Hiển thị chữ nhãn cho dòng tổng
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('')
                            ->formatStateUsing(fn() => 'GRAND TOTAL')
                    ),

                Tables\Columns\TextColumn::make('total_clicked')
                    ->label('Total Clicked')
                    ->money('usd')
                    ->alignment(Alignment::Right),

                Tables\Columns\TextColumn::make('total_missing')
                    ->label('Total Missing')
                    ->money('usd')
                    ->alignment(Alignment::Right),

                Tables\Columns\TextColumn::make('total_pending')
                    ->label('Total Pending')
                    ->money('usd')
                    ->alignment(Alignment::Right),

                Tables\Columns\TextColumn::make('total_confirmed')
                    ->label('Total Confirmed')
                    ->money('usd')
                    ->alignment(Alignment::Right),

                Tables\Columns\TextColumn::make('grand_total')
                    ->label('Total Rebate')
                    ->alignment(Alignment::Right)
                    ->money('usd')
                    ->weight('bold')
                    ->color('primary')
                    // Cột tổng quan trọng nhất
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('')
                            ->money('usd')

                    ),
            ]);
    }
}
