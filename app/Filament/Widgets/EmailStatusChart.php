<?php

namespace App\Filament\Widgets;

use App\Models\Email;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class EmailStatusChart extends ChartWidget
{
    protected int | string | array $columnSpan = 1;

    protected static ?string $heading = 'Email Status Overview';

    protected static ?string $maxHeight = '300px';

    // Livewire property: khi click vào user sẽ lọc chart theo user đó
    public ?string $selectedUser = null;

    /**
     * Khi Admin click vào 1 user name → lọc chart theo user đó
     * Click lần 2 hoặc click "Total Emails" → reset về tổng
     */
    public function onUserClicked(?string $userName): void
    {
        $this->selectedUser = ($this->selectedUser === $userName) ? null : $userName;
    }
    
    protected function getData(): array
    {
        $query = Email::query();

        if (!auth()->user()?->isAdmin()) {
            $query->whereHas('accounts', fn($q) => $q->where('user_id', auth()->id()));
        } elseif ($this->selectedUser) {
            // Admin đang xem chart của 1 user cụ thể
            $query->whereHas('accounts', function ($q) {
                $q->join('users', 'accounts.user_id', '=', 'users.id')
                  ->where('users.name', $this->selectedUser);
            });
        }

        // Live = 'active' HOẶC 'live'
        $live = (clone $query)->whereIn('status', ['active', 'live'])->count();
        $locked = (clone $query)->where('status', 'locked')->count();
        $disabled = (clone $query)->where('status', 'disabled')->count();
        $total = $live + $locked + $disabled;

        $livePer = $total > 0 ? round(($live / $total) * 100, 1) : 0;
        $lockedPer = $total > 0 ? round(($locked / $total) * 100, 1) : 0;
        $disabledPer = $total > 0 ? round(($disabled / $total) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Emails Count',
                    'data' => [$live, $locked, $disabled],
                    'backgroundColor' => ['#4BC0C0', '#FF6384', '#FF9F40'],
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => [
                "Live: ({$livePer}%)",
                "Locked: ({$lockedPer}%)",
                "Disabled: ({$disabledPer}%)",
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * Hiển thị thống kê tổng quan + Holders (clickable per-user)
     */
    public function getDescription(): ?HtmlString
    {
        $isAdmin = auth()->user()?->isAdmin();

        // Nếu đang xem 1 user cụ thể → show detail view
        if ($isAdmin && $this->selectedUser) {
            return $this->renderUserDetailView();
        }

        $query = Email::query();
        if (!$isAdmin) {
            $query->whereHas('accounts', fn($q) => $q->where('user_id', auth()->id()));
        }

        $live = (clone $query)->whereIn('status', ['active', 'live'])->count();
        $locked = (clone $query)->where('status', 'locked')->count();
        $disabled = (clone $query)->where('status', 'disabled')->count();
        $total = (clone $query)->count();

        // Phần tổng quan
        // Phần tổng quan - Dàn hàng ngang để tiết kiệm diện tích và cân bằng chiều cao với widget bên cạnh
        $html = "
        <div class='es-widget-wrapper'>
            <div class='es-header-row'>
                <div style='font-size: 15px; color: #64748b; cursor: pointer; white-space: nowrap;' wire:click=\"onUserClicked(null)\">
                    Total Emails: <span style='color: #1e293b; font-weight: 800;'>{$total}</span>
                </div>
                
                <div style='display: flex; align-items: center; gap: 10px; font-size: 13px; text-transform: uppercase;'>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 7px; height: 7px; background: #4BC0C0; border-radius: 50%;'></div>
                        <span style='font-weight: 700; color: #4BC0C0;'>Live: <span style='color: #1e293b;'>{$live}</span></span>
                    </div>
                    <span style='color: #cbd5e1;'>|</span>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 7px; height: 7px; background: #FF6384; border-radius: 50%;'></div>
                        <span style='font-weight: 700; color: #FF6384;'>Locked: <span style='color: #1e293b;'>{$locked}</span></span>
                    </div>
                    <span style='color: #cbd5e1;'>|</span>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 7px; height: 7px; background: #FF9F40; border-radius: 50%;'></div>
                        <span style='font-weight: 700; color: #FF9F40;'>Disabled: <span style='color: #1e293b;'>{$disabled}</span></span>
                    </div>
                </div>
            </div>
        ";

        // Chi tiết User breakdown (chỉ Admin — clickable)
        if ($isAdmin) {
            $allUsers = User::orderBy('name')->get();

            $emailCounts = Email::query()
                ->join('accounts', 'emails.id', '=', 'accounts.email_id')
                ->select(
                    'accounts.user_id',
                    DB::raw('COUNT(DISTINCT emails.id) as total_count'),
                    DB::raw("COUNT(DISTINCT CASE WHEN emails.status IN ('active','live') THEN emails.id END) as live_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN emails.status = 'locked' THEN emails.id END) as locked_count"),
                    DB::raw("COUNT(DISTINCT CASE WHEN emails.status = 'disabled' THEN emails.id END) as disabled_count"),
                )
                ->whereNotNull('accounts.user_id')
                ->whereNull('accounts.deleted_at')
                ->groupBy('accounts.user_id')
                ->get()
                ->keyBy('user_id');

            $rows = '';
            foreach ($allUsers as $user) {
                $name = e($user->name);
                $initial = strtoupper(mb_substr($name, 0, 1));
                $data = $emailCounts->get($user->id);
                $uTotal = $data->total_count ?? 0;
                $uLive = $data->live_count ?? 0;
                $uLocked = $data->locked_count ?? 0;
                $uDisabled = $data->disabled_count ?? 0;

                $rows .= "
                    <div class='es-holder-row' wire:click=\"onUserClicked('{$name}')\">
                        <div class='es-initials-circle'>
                            <span class='es-initials-text'>{$initial}</span>
                        </div>
                        <span class='es-name' style='font-size: 11px; font-weight: 600; color: #334155; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;'>{$name}</span>
                        
                        <div class='es-stats-group'>
                            <span class='es-stat-badge' style='color: #475569; background: #f1f5f9;'>Total: {$uTotal}</span>
                            <span class='es-stat-badge' style='color: #4BC0C0; background: rgba(75,192,192,0.1);'>Live: {$uLive}</span>
                            <span class='es-stat-badge' style='color: #FF6384; background: rgba(255,99,132,0.1);'>Locked: {$uLocked}</span>
                            <span class='es-stat-badge' style='color: #FF9F40; background: rgba(255,159,64,0.1);'>Disabled: {$uDisabled}</span>
                        </div>
                    </div>
                ";
            }

            // Tính toán Unassigned Emails: Lấy tổng hệ thống trừ đi tổng của các users đã được liệt kê
            $assignedTotal = $emailCounts->sum('total_count');
            $assignedLive = $emailCounts->sum('live_count');
            $assignedLocked = $emailCounts->sum('locked_count');
            $assignedDisabled = $emailCounts->sum('disabled_count');

            $unassignedTotal = max(0, $total - $assignedTotal);
            
            if ($unassignedTotal > 0) {
                // Đảm bảo số liệu cộng lại luôn chuẩn xác 100% bằng phép trừ
                $uLive = max(0, $live - $assignedLive);
                $uLocked = max(0, $locked - $assignedLocked);
                $uDisabled = max(0, $disabled - $assignedDisabled);

                $rows .= "
                    <div class='es-holder-row' style='border-top: 1px solid #f1f5f9; margin-top: 2px; cursor: default; background: transparent !important;'>
                        <div style='width: 20px; height: 20px; background: #94a3b8; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;'>
                            <span style='font-size: 9px; font-weight: 700; color: white;'>?</span>
                        </div>
                        <span style='font-size: 11px; font-weight: 600; color: #64748b; font-style: italic;'>Unassigned</span>
                        
                        <div class='es-stats-group'>
                            <span class='es-stat-badge' style='color: #475569; background: #f1f5f9;'>Total: {$unassignedTotal}</span>
                            <span class='es-stat-badge' style='color: #4BC0C0; background: rgba(75,192,192,0.1);'>Live: {$uLive}</span>
                            <span class='es-stat-badge' style='color: #FF6384; background: rgba(255,99,132,0.1);'>Locked: {$uLocked}</span>
                            <span class='es-stat-badge' style='color: #FF9F40; background: rgba(255,159,64,0.1);'>Disabled: {$uDisabled}</span>
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
        $emailQuery = Email::query()
            ->whereHas('accounts', function ($q) {
                $q->join('users', 'accounts.user_id', '=', 'users.id')
                  ->where('users.name', $this->selectedUser);
            });

        $live = (clone $emailQuery)->whereIn('status', ['active', 'live'])->count();
        $locked = (clone $emailQuery)->where('status', 'locked')->count();
        $disabled = (clone $emailQuery)->where('status', 'disabled')->count();
        $userTotal = $live + $locked + $disabled;

        $statusHtml = '';
        $statuses = [
            ['Live', $live, '#4BC0C0'],
            ['Locked', $locked, '#FF6384'],
            ['Disabled', $disabled, '#FF9F40'],
        ];

        foreach ($statuses as $i => [$label, $count, $color]) {
            if ($i > 0) {
                $statusHtml .= "<div style='width: 1px; height: 20px; background: #e2e8f0; align-self: center;'></div>";
            }
            $statusHtml .= "
                <div style='display: flex; flex-direction: column;'>
                    <div style='display: flex; align-items: center; gap: 4px;'>
                        <div style='width: 8px; height: 8px; background: {$color}; border-radius: 50%;'></div>
                        <span style='font-size: 11px; font-weight: 600; color: {$color};'>{$label}</span>
                    </div>
                    <div style='padding-left: 12px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$count}</div>
                </div>
            ";
        }

        return new HtmlString("
            <div class='mt-2 space-y-3'>
                <div style='display: flex; align-items: center; gap: 10px; margin-bottom: 8px;'>
                    <button wire:click=\"onUserClicked(null)\" style='padding: 2px 8px; background: #f1f5f9; border-radius: 6px; font-size: 11px; font-weight: bold; color: #475569; cursor: pointer; border: 1px solid #e2e8f0;'>← BACK</button>
                    <span style='font-size: 15px; font-weight: 600; color: #1e293b;'>{$this->selectedUser} ({$userTotal})</span>
                </div>
                <div style='display: flex; gap: 15px; flex-wrap: wrap;'>{$statusHtml}</div>
            </div>
        ");
    }
}
