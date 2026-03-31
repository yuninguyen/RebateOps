<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB; // Cần thiết để dùng các hàm tính toán của Database

class AccountOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = [];

        // Trình dựng Giao diện (HTML) giống EmailStatusChart
        $buildHtml = function ($active, $banned, $available) {
            $lblLive = __('system.status.live');
            $lblBanned = __('system.status.banned');
            $lblAvailable = __('system.unassigned');
            
            return new \Illuminate\Support\HtmlString("
                <div style='display: flex; gap: 12px; align-items: center; margin-top: 8px;'>
                    <div style='display: flex; flex-direction: column; gap: 2px;'>
                        <div style='display: flex; align-items: center; gap: 6px;'>
                            <div style='width: 8px; height: 8px; background: #4BC0C0; border-radius: 50%;'></div>
                            <span style='font-size: 11px; font-weight: 600; color: #4BC0C0; text-transform: uppercase; letter-spacing: 0.05em;'>{$lblLive}</span>
                        </div>
                        <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$active}</div>
                    </div>

                    <div style='width: 1px; height: 24px; background: #e2e8f0;'></div>

                    <div style='display: flex; flex-direction: column; gap: 2px;'>
                        <div style='display: flex; align-items: center; gap: 6px;'>
                            <div style='width: 8px; height: 8px; background: #FF6384; border-radius: 50%;'></div>
                            <span style='font-size: 11px; font-weight: 600; color: #FF6384; text-transform: uppercase; letter-spacing: 0.05em;'>{$lblBanned}</span>
                        </div>
                        <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$banned}</div>
                    </div>

                    <div style='width: 1px; height: 24px; background: #e2e8f0;'></div>

                    <div style='display: flex; flex-direction: column; gap: 2px;'>
                        <div style='display: flex; align-items: center; gap: 6px;'>
                            <div style='width: 8px; height: 8px; background: #FF9F40; border-radius: 50%;'></div>
                            <span style='font-size: 11px; font-weight: 600; color: #FF9F40; text-transform: uppercase; letter-spacing: 0.05em;'>{$lblAvailable}</span>
                        </div>
                        <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$available}</div>
                    </div>
                </div>
            ");
        };

        // Lấy danh sách tất cả các nền tảng hiện có
        $platforms = Account::select('platform')
            ->distinct()
            ->whereNotNull('platform')
            ->pluck('platform');

        // Tạo 1 thẻ đầu tiên cho bảng xếp hạng tổng toàn hệ thống
        $totalSystem = Account::count();
        $totalAvailable = Account::whereNull('user_id')->count();
        $myTotalSystem = Account::where('user_id', auth()->id())->count();
        $myBannedSystem = Account::where('user_id', auth()->id())->whereJsonContains('status', 'banned')->count();
        $myActiveSystem = $myTotalSystem - $myBannedSystem;

        $stats[] = Stat::make('ALL PLATFORMS (GLOBAL)', $totalSystem)
            ->description($buildHtml($myActiveSystem, $myBannedSystem, $totalAvailable))
            ->color('gray');

        // Tạo các thẻ vòng lặp cho từng nền tảng
        foreach ($platforms as $platform) {
            
            $query = Account::where('platform', $platform);
            
            $total = (clone $query)->count();
            $available = (clone $query)->whereNull('user_id')->count();
            
            $myQuery = (clone $query)->where('user_id', auth()->id());
            $myTotal = (clone $myQuery)->count();
            $myBanned = (clone $myQuery)->whereJsonContains('status', 'banned')->count();
            $myActive = $myTotal - $myBanned;
            
            $stats[] = Stat::make($platform, $total)
                ->description($buildHtml($myActive, $myBanned, $available))
                ->color('primary');
        }

        return $stats;
    }
}