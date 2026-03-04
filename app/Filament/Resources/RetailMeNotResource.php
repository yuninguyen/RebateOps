<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RetailMeNotResource\Pages;
use App\Filament\Resources\RetailMeNotResource\RelationManagers;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\Alignment;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn; 
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Traits\HasAccountSchema; // <-- Nhúng Trait
use App\Filament\Resources\AccountResource\RelationManagers\ActivitiesRelationManager;

class RetailMeNotResource extends Resource
{
    use HasAccountSchema; // <-- Dòng ma thuật: Gọi toàn bộ Form, Table, Infolist vào đây!
    
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'RESOURCE HUB';
    protected static ?string $navigationLabel = 'RetailMeNot';
    protected static ?string $navigationParentItem = 'All Platforms';
    protected static ?int $navigationSort = 2;
    
    // Thêm dòng này để thu gọn menu bên trái, nhường chỗ cho bảng
    protected static bool $isScopedToTenant = false;
    // THÊM DÒNG NÀY: Đổi đường dẫn URL từ /retailmenot thành /retailmenot
    protected static ?string $slug = 'retailmenot';

    // HÀM LỌC DỮ LIỆU: Chỉ lấy tài khoản của RetailMeNot
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('platform', 'RetailMeNot');
    }
    
    protected function getRedirectUrl(): string
    {
        // Quay về trang danh sách (List View)
        return $this->getResource()::getUrl('index');
    }

    public static function getRelations(): array
{
    return [
        ActivitiesRelationManager::class, // <-- Gắn bảng Lịch sử vào đây
    ];
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRetailMeNots::route('/'),
            'create' => Pages\CreateRetailMeNot::route('/create'),
            'edit' => Pages\EditRetailMeNot::route('/{record}/edit'),
        ];
    }
}
