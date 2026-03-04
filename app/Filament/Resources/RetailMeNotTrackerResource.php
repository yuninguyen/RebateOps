<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RetailMeNotTrackerResource\Pages;
use App\Filament\Resources\RetailMeNotTrackerResource\RelationManagers;
use App\Models\RetailMeNotTracker;
use App\Models\RebateTracker;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\Traits\HasTrackerSchema;
use App\Filament\Resources\RebateTrackerResource\RelationManagers\ActivitiesRelationManager;

class RetailMeNotTrackerResource extends Resource
{
    use HasTrackerSchema; // <-- Dòng ma thuật: Gọi toàn bộ Form, Table, Infolist vào đây!
    
    protected static ?string $model = RebateTracker::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'WORKING SPACE';
    protected static ?string $navigationLabel = 'RetailMeNot Tracker';
    protected static ?string $navigationParentItem = 'All Rebate Tracker';
    protected static ?int $navigationSort = 2;

    // Thêm dòng này để thu gọn menu bên trái, nhường chỗ cho bảng
    protected static bool $isScopedToTenant = false;
    // THÊM DÒNG NÀY: Đổi đường dẫn URL từ /rakutens thành /rakuten
    protected static ?string $slug = 'retailmenot-tracker';

    // HÀM LỌC DỮ LIỆU: Chỉ lấy tài khoản của Rakuten
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('account', function ($query) {
            $query->where('platform', 'RetailMeNot');
        });
    }

    public static function getRelations(): array
{
    return [
        ActivitiesRelationManager::class, // <-- Thêm dòng này vào
    ];
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRetailMeNotTrackers::route('/'),
            'create' => Pages\CreateRetailMeNotTracker::route('/create'),
            'edit' => Pages\EditRetailMeNotTracker::route('/{record}/edit'),
        ];
    }
}
