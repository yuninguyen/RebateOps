<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\KeyValue;

class ActivitiesRelationManager extends RelationManager
{
    protected static string $relationship = 'activities';

    protected static ?string $title = 'Account Activity History';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('description')
            ->columns([
                // 1. NGƯỜI THỰC HIỆN (Causer)
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->placeholder('Systemed User') // Nếu không có user đăng nhập (chạy ngầm)
                    ->weight('bold')
                    ->color('primary'),

                // 2. HÀNH ĐỘNG (Ví dụ: updated, created)
                Tables\Columns\TextColumn::make('description')
                    ->label('Hành động')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state)),

                // 3. THỜI GIAN
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Thời gian')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc') // Mặc định xếp log mới nhất lên đầu
            ->filters([
                //
            ])
            ->headerActions([
                // Xóa nút Create vì log là tự động ghi, không cho phép nhập tay
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Có thể thêm nút View để xem chi tiết dữ liệu cũ/mới nếu muốn
                Tables\Actions\ViewAction::make()
                    ->label('View detail')
                    ->modalHeading('Details of data changes')
                    ->modalWidth('4xl') // Mở rộng popup cho dễ nhìn
                    ->mutateRecordDataUsing(function (array $data, $record): array {
                        // Tách dữ liệu cũ và mới từ mảng lịch sử
                        $data['old_data'] = $record->properties['old'] ?? [];
                        $data['new_data'] = $record->properties['attributes'] ?? [];

                        return $data;
                    })
                    ->form([
                        Grid::make(2)->schema([
                            KeyValue::make('old_data')
                                ->label('Old data'),

                            KeyValue::make('new_data')
                                ->label('New data'),
                        ])
                    ]),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
