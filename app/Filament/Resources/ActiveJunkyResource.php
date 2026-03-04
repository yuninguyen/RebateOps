<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActiveJunkyResource\Pages;
use App\Filament\Resources\ActiveJunkyResource\RelationManagers;
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

class ActiveJunkyResource extends Resource
{
    use HasAccountSchema; // <-- Dòng ma thuật: Gọi toàn bộ Form, Table, Infolist vào đây!
    
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'RESOURCE HUB';
    protected static ?string $navigationLabel = 'Active Junky';
    protected static ?string $navigationParentItem = 'All Platforms';
    protected static ?int $navigationSort = 4;
    
    // Thêm dòng này để thu gọn menu bên trái, nhường chỗ cho bảng
    protected static bool $isScopedToTenant = false;
    // THÊM DÒNG NÀY: Đổi đường dẫn URL từ /Active Junky thành /Active Junky
    protected static ?string $slug = 'active-junky';

    // HÀM LỌC DỮ LIỆU: Chỉ lấy tài khoản của Active Junky
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('platform', 'Active Junky');
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
            'index' => Pages\ListActiveJunkies::route('/'),
            'create' => Pages\CreateActiveJunky::route('/create'),
            'edit' => Pages\EditActiveJunky::route('/{record}/edit'),
        ];
    }
}
