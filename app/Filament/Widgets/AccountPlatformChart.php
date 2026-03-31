<?php

namespace App\Filament\Widgets;

use App\Models\Account;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class AccountPlatformChart extends ChartWidget
{
    protected static ?string $heading = 'Platform Distribution';
    protected int | string | array $columnSpan = 1;
    protected static ?int $sort = 2;
    protected static ?string $maxHeight = '300px';

    public ?string $selectedUser = null;

    // Danh sách màu bạn yêu cầu
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

        // Label cho Chart: Tên (Percent%) để Hover hiện đúng ý bạn
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
        $query = Account::query();
        if (!auth()->user()?->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        $totalAccounts = (clone $query)->count();

        if ($this->selectedUser) {
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
                        <button wire:click=\"onUserClicked(null)\" style='padding: 2px 8px; background: #f1f5f9; border-radius: 6px; font-size: 11px; font-weight: bold; color: #475569; cursor: pointer;'>← BACK</button>
                        <span style='font-size: 15px; font-weight: 600; color: #1e293b;'>{$this->selectedUser} ({$userTotal})</span>
                    </div>
                    <div style='display: flex; gap: 15px; flex-wrap: wrap;'>{$platformHtml}</div>
                </div>");
        }

        $usersQuery = User::query();
        if (!auth()->user()?->isAdmin()) {
            $usersQuery->where('id', auth()->id());
        }
        $users = $usersQuery->withCount('accounts')->get();

        $userHtml = $users->map(function ($user) {
            return "
                <div style='display: flex; flex-direction: column; cursor: pointer;' wire:click=\"onUserClicked('{$user->name}')\">
                    <div style='display: flex; align-items: center; gap: 6px;'>
                        <div style='width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;'></div>
                        <span style='font-size: 11px; font-weight: 600; color: #3b82f6; text-transform: uppercase;'>{$user->name}</span>
                    </div>
                    <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$user->accounts_count}</div>
                </div>";
        })->implode("<div style='width: 1px; height: 20px; background: #e2e8f0; align-self: center;'></div>");

        return new HtmlString("
            <div class='mt-2 space-y-3'>
                <div style='font-size: 15px; color: #64748b; letter-spacing: -0.01em;'>
                    Total Emails: <span style='color: #1e293b; font-weight: 700;'>{$totalAccounts}</span>
                </div>
                
                <div style='display: flex; gap: 15px; flex-wrap: wrap;'>{$userHtml}</div>
                
            </div>");
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
                // Cách tiếp cận an toàn hơn cho Tooltip trong Filament
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
