<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AccountOverview extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $stats = [];
        $isAdmin = auth()->user()?->isAdmin();

        // Trình dựng Giao diện (HTML) cho phần tổng quan Live/Banned/Unassigned
        $buildSummaryHtml = function ($active, $banned, $available) {
            $lblLive = __('system.status.live');
            $lblBanned = __('system.status.banned');
            $lblAvailable = __('system.unassigned');

            return "
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
            ";
        };

        // Trình dựng HTML chi tiết theo từng User — hiển thị TẤT CẢ user
        $buildUserDetailHtml = function ($allUsers, $dataMap, $unassignedData = null) {
            $rows = '';
            foreach ($allUsers as $user) {
                $name = e($user->name);
                $initial = strtoupper(mb_substr($name, 0, 1));
                $data = $dataMap->get($user->id);
                $total = $data->total_count ?? 0;
                $live = $data->live_count ?? 0;
                $banned = $data->banned_count ?? 0;

                $rows .= "
                    <div style='display: grid; grid-template-columns: 20px 100px 1fr 1fr 1fr; align-items: center; gap: 8px; padding: 4px 0;'>
                        <div style='width: 20px; height: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                            <span style='font-size: 9px; font-weight: 700; color: white; line-height: 1;'>{$initial}</span>
                        </div>
                        <span style='font-size: 11px; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$name}</span>
                        <span style='font-size: 10px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 1px 4px; border-radius: 4px; text-align: center; width: 100%; white-space: nowrap;'>Total: {$total}</span>
                        <span style='font-size: 10px; font-weight: 600; color: #4BC0C0; background: rgba(75,192,192,0.1); padding: 1px 4px; border-radius: 4px; text-align: center; width: 100%; white-space: nowrap;'>Live: {$live}</span>
                        <span style='font-size: 10px; font-weight: 600; color: #FF6384; background: rgba(255,99,132,0.1); padding: 1px 4px; border-radius: 4px; text-align: center; width: 100%; white-space: nowrap;'>Banned: {$banned}</span>
                    </div>
                ";
            }

            // Thêm hàng Unassigned nếu có
            if ($unassignedData && $unassignedData['total'] > 0) {
                $rows .= "
                    <div style='display: grid; grid-template-columns: 20px 100px 1fr 1fr 1fr; align-items: center; gap: 8px; padding: 4px 0; border-top: 1px solid #f1f5f9; margin-top: 2px;'>
                        <div style='width: 20px; height: 20px; background: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                            <span style='font-size: 9px; font-weight: 700; color: white;'>?</span>
                        </div>
                        <span style='font-size: 11px; font-weight: 600; color: #64748b; font-style: italic;'>Unassigned</span>
                        <span style='font-size: 10px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 1px 4px; border-radius: 4px; text-align: center; width: 100%;'>Total: {$unassignedData['total']}</span>
                        <span style='font-size: 10px; font-weight: 600; color: #4BC0C0; background: rgba(75,192,192,0.1); padding: 1px 4px; border-radius: 4px; text-align: center; width: 100%;'>Live: {$unassignedData['live']}</span>
                        <span style='font-size: 10px; font-weight: 600; color: #FF6384; background: rgba(255,99,132,0.1); padding: 1px 4px; border-radius: 4px; text-align: center; width: 100%;'>Banned: {$unassignedData['banned']}</span>
                    </div>
                ";
            }

            return "
                <div style='margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0;'>
                    <div style='font-size: 9px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 4px;'>Holders</div>
                    {$rows}
                </div>
            ";
        };

        // Helper: Tính tổng Live/Banned cho một query set (GLOBAL, không lọc theo user đăng nhập)
        // Live = status chứa 'active' HOẶC 'used' (In Use)
        $countGlobalStats = function ($baseQuery) {
            $totalLive = (clone $baseQuery)->where(function ($q) {
                $q->whereJsonContains('status', 'active')
                  ->orWhereJsonContains('status', 'used');
            })->count();
            $totalBanned = (clone $baseQuery)->whereJsonContains('status', 'banned')->count();
            $totalUnassigned = (clone $baseQuery)->whereNull('user_id')->count();
            return [$totalLive, $totalBanned, $totalUnassigned];
        };

        // Truy vấn dữ liệu chi tiết: Tính số account mỗi user nắm, trên từng platform
        $userPlatformData = Account::query()
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->select(
                'accounts.platform',
                'accounts.user_id',
                'users.name as user_name',
                DB::raw('COUNT(*) as total_count'),
                DB::raw("SUM(CASE WHEN JSON_CONTAINS(accounts.status, '\"active\"') OR JSON_CONTAINS(accounts.status, '\"used\"') THEN 1 ELSE 0 END) as live_count"),
                DB::raw("SUM(CASE WHEN JSON_CONTAINS(accounts.status, '\"banned\"') THEN 1 ELSE 0 END) as banned_count"),
            )
            ->whereNotNull('accounts.user_id')
            ->groupBy('accounts.platform', 'accounts.user_id', 'users.name')
            ->orderBy('users.name')
            ->get();

        // Lấy TẤT CẢ users
        $allUsers = User::orderBy('name')->get();

        // Lấy danh sách tất cả các nền tảng hiện có
        $platforms = Account::select('platform')
            ->distinct()
            ->whereNotNull('platform')
            ->pluck('platform');

        // === THẺ ĐẦU TIÊN: ALL PLATFORMS (GLOBAL) ===
        $totalSystem = Account::count();

        if ($isAdmin) {
            // Admin: hiển thị tổng GLOBAL
            [$globalLive, $globalBanned, $globalUnassigned] = $countGlobalStats(Account::query());

            $allUsersGlobalData = Account::query()
                ->join('users', 'accounts.user_id', '=', 'users.id')
                ->select(
                    'accounts.user_id',
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw("SUM(CASE WHEN JSON_CONTAINS(accounts.status, '\"active\"') OR JSON_CONTAINS(accounts.status, '\"used\"') THEN 1 ELSE 0 END) as live_count"),
                    DB::raw("SUM(CASE WHEN JSON_CONTAINS(accounts.status, '\"banned\"') THEN 1 ELSE 0 END) as banned_count"),
                )
                ->whereNotNull('accounts.user_id')
                ->groupBy('accounts.user_id')
                ->get()
                ->keyBy('user_id');

            $globalHtml = $buildSummaryHtml($globalLive, $globalBanned, $globalUnassigned);
            $globalHtml .= $buildUserDetailHtml($allUsers, $allUsersGlobalData, [
                'total' => $globalUnassigned,
                'live' => max(0, $globalLive - $allUsersGlobalData->sum('live_count')),
                'banned' => max(0, $globalBanned - $allUsersGlobalData->sum('banned_count'))
            ]);
        } else {
            // Staff: hiển thị số liệu cá nhân
            $myTotal = Account::where('user_id', auth()->id())->count();
            $myBanned = Account::where('user_id', auth()->id())->whereJsonContains('status', 'banned')->count();
            $myActive = $myTotal - $myBanned;
            $totalUnassigned = Account::whereNull('user_id')->count();
            $globalHtml = $buildSummaryHtml($myActive, $myBanned, $totalUnassigned);
        }

        $stats[] = Stat::make('ALL PLATFORMS (GLOBAL)', $totalSystem)
            ->description(new \Illuminate\Support\HtmlString($globalHtml))
            ->color('gray');

        // === CÁC THẺ TỪNG PLATFORM ===
        foreach ($platforms as $platform) {
            $query = Account::where('platform', $platform);
            $total = (clone $query)->count();

            if ($isAdmin) {
                // Admin: hiển thị tổng GLOBAL cho platform này
                [$platformLive, $platformBanned, $platformUnassigned] = $countGlobalStats(clone $query);
                $platformHtml = $buildSummaryHtml($platformLive, $platformBanned, $platformUnassigned);

                // Tạo data map cho platform này từ userPlatformData
                $platformDataMap = $userPlatformData
                    ->where('platform', $platform)
                    ->keyBy('user_id');
                $platformHtml .= $buildUserDetailHtml($allUsers, $platformDataMap, [
                    'total' => $platformUnassigned,
                    'live' => max(0, $platformLive - $platformDataMap->sum('live_count')),
                    'banned' => max(0, $platformBanned - $platformDataMap->sum('banned_count'))
                ]);
            } else {
                // Staff: hiển thị số liệu cá nhân
                $available = (clone $query)->whereNull('user_id')->count();
                $myQuery = (clone $query)->where('user_id', auth()->id());
                $myTotal = (clone $myQuery)->count();
                $myBanned = (clone $myQuery)->whereJsonContains('status', 'banned')->count();
                $myActive = $myTotal - $myBanned;
                $platformHtml = $buildSummaryHtml($myActive, $myBanned, $available);
            }

            $stats[] = Stat::make($platform, $total)
                ->description(new \Illuminate\Support\HtmlString($platformHtml))
                ->color('primary');
        }

        return $stats;
    }
}
