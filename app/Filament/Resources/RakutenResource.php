<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RakutenResource\Pages;
use App\Models\Account; // Use the Account model
use Filament\Forms\Form; // Correct Form import
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table; // Correct Table import
use Filament\Tables;
use Filament\Support\Enums\Alignment; // Quan trọng: Phải import cái này
use Filament\Tables\Columns\TextColumn; // Fix for image_f54065
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\Traits\HasAccountSchema; // <-- Nhúng Trait
use App\Filament\Resources\AccountResource\RelationManagers\ActivitiesRelationManager;

class RakutenResource extends Resource
{
    use HasAccountSchema; // <-- Dòng ma thuật: Gọi toàn bộ Form, Table, Infolist vào đây!
        
    protected static ?string $model = Account::class; // SỬA DÒNG NÀY: Trỏ về Account model

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'RESOURCE HUB';
    protected static ?string $navigationLabel = 'Rakuten';
    protected static ?string $navigationParentItem = 'All Platforms';
    protected static ?int $navigationSort = 1;

    // Thêm dòng này để thu gọn menu bên trái, nhường chỗ cho bảng
    protected static bool $isScopedToTenant = false;
    // THÊM DÒNG NÀY: Đổi đường dẫn URL từ /rakutens thành /rakuten
    protected static ?string $slug = 'rakuten';

    // HÀM LỌC DỮ LIỆU: Chỉ lấy tài khoản của Rakuten
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('platform', 'Rakuten');
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
            'index' => Pages\ListRakutens::route('/'),
            'create' => Pages\CreateRakuten::route('/create'),
            'edit' => Pages\EditRakuten::route('/{record}/edit'),
        ];
    }
}
