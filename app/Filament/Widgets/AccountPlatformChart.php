<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class AccountPlatformChart extends ChartWidget
{
    protected static ?string $heading = 'Platform Distribution';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    public ?string $selectedUser = null;

    // Danh sách màu
    protected static array $myColors = ['#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'];

    public function onUserClicked(?string $userName): void
    {
        $this->selectedUser = ($this->selectedUser === $userName) ? null : $userName;
    }

    protected function getData(): array
    {
        $query = Account::query();

        if (!auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        } elseif ($this->selectedUser) {
            $query->join('users', 'accounts.user_id', '=', 'users.id')
                ->where('users.name', $this->selectedUser);
        }

        $data = $query->selectRaw('platform as label, count(*) as total')
            ->groupBy('platform')
            ->orderBy('total', 'desc')
            ->pluck('total', 'label');

        $totalSum = $data->sum();

        $labels = $data->map(function ($value, $key) use ($totalSum) {
            $percent = $totalSum > 0 ? round(($value / $totalSum) * 100, 1) : 0;
            return "{$key} ({$percent}%)";
        })->values()->toArray();

        return [
            'datasets' => [[
                'data' => $data->values()->toArray(),
                'backgroundColor' => array_slice(self::$myColors, 0, $data->count()),
                'hoverOffset' => 15,
            ]],
            'labels' => $labels,
        ];
    }

    public function getDescription(): ?HtmlString
    {
        $isAdmin = auth()->user()?->isAdmin();

        // Nếu đang xem 1 user cụ thể → show detail view
        if ($isAdmin && $this->selectedUser) {
            return $this->renderUserDetailView();
        }

        $query = Account::query();
        if (!$isAdmin) {
            $query->where('user_id', auth()->id());
        }
        $totalAccounts = (clone $query)->count();

        // Lấy số liệu tổng quan hệ thống để hiển thị ở header (giúp cân bằng chiều cao với widget bên cạnh)
        $totalGlobalLive = (clone $query)->where(function($q) {
            $q->whereJsonContains('status', 'active')->orWhereJsonContains('status', 'used');
        })->count();
        $totalGlobalBanned = (clone $query)->whereJsonContains('status', 'banned')->count();

        // 1. Phần tổng quan Header
        $html = "
        <div class='es-widget-wrapper'>
            <div class='es-header-row'>
                <div style='font-size: 15px; color: #64748b; cursor: pointer; white-space: nowrap;' wire:click=\"onUserClicked(null)\">
                    Total Accounts: <span style='color: #1e293b; font-weight: 800;'>{$totalAccounts}</span>
                </div>
                
                <div style='display: flex; align-items: center; gap: 10px; font-size: 13px; text-transform: uppercase;'>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 7px; height: 7px; background: #4BC0C0; border-radius: 50%;'></div>
                        <span style='font-weight: 700; color: #4BC0C0;'>Live: <span style='color: #1e293b;'>{$totalGlobalLive}</span></span>
                    </div>
                    <span style='color: #cbd5e1;'>|</span>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 7px; height: 7px; background: #FF6384; border-radius: 50%;'></div>
                        <span style='font-weight: 700; color: #FF6384;'>Banned: <span style='color: #1e293b;'>{$totalGlobalBanned}</span></span>
                    </div>
                </div>
            </div>
        ";

        // 2. Chi tiết User breakdown (chỉ Admin — clickable)
        if ($isAdmin) {
            $allUsers = User::orderBy('name')->get();

            $accountCounts = Account::query()
                ->select(
                    'user_id',
                    DB::raw('COUNT(*) as total_count'),
                    DB::raw("SUM(CASE WHEN JSON_CONTAINS(status, '\"active\"') OR JSON_CONTAINS(status, '\"used\"') THEN 1 ELSE 0 END) as live_count"),
                    DB::raw("SUM(CASE WHEN JSON_CONTAINS(status, '\"banned\"') THEN 1 ELSE 0 END) as banned_count"),
                )
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $rows = '';
            foreach ($allUsers as $user) {
                $name = e($user->name);
                $initial = strtoupper(mb_substr($name, 0, 1));
                $data = $accountCounts->get($user->id);
                $uTotal = $data->total_count ?? 0;
                $uLive = $data->live_count ?? 0;
                $uBanned = $data->banned_count ?? 0;

                $rows .= "
                    <div class='es-holder-row' wire:click=\"onUserClicked('{$name}')\">
                        <div class='es-initials-circle'>
                            <span class='es-initials-text'>{$initial}</span>
                        </div>
                        <span class='es-name' style='font-size: 11px; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$name}</span>
                        
                        <div class='es-stats-group'>
                            <span class='es-stat-badge' style='color: #475569; background: #f1f5f9;'>Total: {$uTotal}</span>
                            <span class='es-stat-badge' style='color: #4BC0C0; background: rgba(75,192,192,0.1);'>Live: {$uLive}</span>
                            <span class='es-stat-badge' style='color: #FF6384; background: rgba(255,99,132,0.1);'>Banned: {$uBanned}</span>
                            <span class='es-stat-badge' style='visibility: hidden;'>-</span> <!-- Giữ chỗ để cân bằng grid 4 cột stats -->
                        </div>
                    </div>
                ";
            }

            // Tính toán Unassigned Accounts: Lấy tổng hệ thống trừ đi tổng của các users đã được liệt kê
            $assignedTotal = $accountCounts->sum('total_count');
            $assignedLive = $accountCounts->sum('live_count');
            $assignedBanned = $accountCounts->sum('banned_count');

            $unassignedTotal = max(0, $totalAccounts - $assignedTotal);
            
            if ($unassignedTotal > 0) {
                $uLive = max(0, $totalGlobalLive - $assignedLive);
                $uBanned = max(0, $totalGlobalBanned - $assignedBanned);

                $rows .= "
                    <div class='es-holder-row' style='border-top: 1px solid #f1f5f9; margin-top: 2px; cursor: default; background: transparent !important;'>
                        <div style='width: 20px; height: 20px; background: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                            <span style='font-size: 9px; font-weight: 700; color: white;'>?</span>
                        </div>
                        <span style='font-size: 11px; font-weight: 600; color: #64748b; font-style: italic;'>Unassigned</span>
                        
                        <div class='es-stats-group'>
                            <span class='es-stat-badge' style='color: #475569; background: #f1f5f9;'>Total: {$unassignedTotal}</span>
                            <span class='es-stat-badge' style='color: #4BC0C0; background: rgba(75,192,192,0.1);'>Live: {$uLive}</span>
                            <span class='es-stat-badge' style='color: #FF6384; background: rgba(255,99,132,0.1);'>Banned: {$uBanned}</span>
                            <span class='es-stat-badge' style='visibility: hidden;'>-</span>
                        </div>
                    </div>
                ";
            }

            $html .= "
                <div class='es-holders-container'>
                    <div class='es-holder-label'>Holders <span style='font-size: 8px; color: #cbd5e1;'>(click to filter)</span></div>
                    {$rows}
                </div>
            ";
        }

        $html .= "</div>";

        return new HtmlString($html);
    }

    /**
     * View chi tiết khi Admin click vào 1 user cụ thể
     */
    private function renderUserDetailView(): HtmlString
    {
        $platforms = Account::query()
            ->join('users', 'accounts.user_id', '=', 'users.id')
            ->where('users.name', $this->selectedUser)
            ->selectRaw('platform, count(*) as total')
            ->groupBy('platform')
            ->get();

        $userTotal = $platforms->sum('total');
        $platformHtml = $platforms->map(function ($p, $index) {
            $color = self::$myColors[$index % count(self::$myColors)];
            return "
                <div style='display: flex; flex-direction: column;'>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 8px; height: 8px; background: {$color}; border-radius: 50%;'></div>
                        <span style='font-size: 11px; font-weight: 600; color: #64748b;'>{$p->platform}</span>
                    </div>
                    <div style='padding-left: 12px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$p->total}</div>
                </div>";
        })->implode("<div style='width: 1px; height: 20px; background: #e2e8f0; align-self: center;'></div>");

        return new HtmlString("
            <div class='mt-2 space-y-3'>
                <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 8px;'>
                    <button wire:click=\"onUserClicked(null)\" style='padding: 2px 8px; background: #f1f5f9; border-radius: 6px; font-size: 11px; font-weight: bold; color: #475569; cursor: pointer; border: 1px solid #e2e8f0;'>← BACK</button>
                    <span style='font-size: 15px; font-weight: 600; color: #1e293b;'>{$this->selectedUser} ({$userTotal})</span>
                </div>
                <div style='display: flex; gap: 15px; flex-wrap: wrap;'>{$platformHtml}</div>
            </div>
        ");
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                ],
                'tooltip' => [
                    'enabled' => true,
                    'mode' => 'index',
                    'intersect' => true,
                ],
                'datalabels' => [
                    'display' => true,
                    'color' => '#fff',
                    'font' => ['weight' => 'bold', 'size' => 11],
                    'formatter' => "function(value, context) {
                        let sum = context.dataset.data.reduce((a, b) => a + b, 0);
                        return Math.round((value / sum) * 100) + '%';
                    }",
                ],
            ],
        ];
    }
}
