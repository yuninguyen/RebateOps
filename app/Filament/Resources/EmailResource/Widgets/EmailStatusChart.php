<?php

namespace App\Filament\Resources\EmailResource\Widgets;

use App\Models\Email;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\HtmlString;

class EmailStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Email Status Overview';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // 1. Lấy số lượng thực tế
        $live = Email::where('status', 'active')->count();
        $locked = Email::where('status', 'locked')->count();
        $disabled = Email::where('status', 'disabled')->count();
        $total = $live + $locked + $disabled;

        // 2. Tính toán phần trăm (%)
        $livePer = $total > 0 ? round(($live / $total) * 100, 1) : 0;
        $lockedPer = $total > 0 ? round(($locked / $total) * 100, 1) : 0;
        $disabledPer = $total > 0 ? round(($disabled / $total) * 100, 1) : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Emails Count',
                    'data' => [$live, $locked, $disabled],
                    'backgroundColor' => ['#22c55e', '#ef4444', '#f59e0b'],
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
     * Sửa lỗi hiển thị xuống dòng và màu sắc
     */
public function getDescription(): ?HtmlString
{
    $live = Email::where('status', 'active')->count();
    $locked = Email::where('status', 'locked')->count();
    $disabled = Email::where('status', 'disabled')->count();
    $total = Email::count();

    return new HtmlString("
        <div class='mt-2 space-y-3'>
            <div style='font-size: 15px; color: #64748b; letter-spacing: -0.01em;'>
                Total Emails: <span style='color: #1e293b; font-weight: 700;'>{$total}</span>
            </div>
            
            <div style='display: flex; gap: 12px; align-items: center;'>
                <div style='display: flex; flex-direction: column; gap: 2px;'>
                    <div style='display: flex; align-items: center; gap: 6px;'>
                        <div style='width: 8px; height: 8px; background: #22c55e; border-radius: 50%;'></div>
                        <span style='font-size: 11px; font-weight: 600; color: #22c55e; text-transform: uppercase; letter-spacing: 0.05em;'>Live</span>
                    </div>
                    <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$live}</div>
                </div>

                <div style='width: 1px; height: 24px; background: #e2e8f0;'></div>

                <div style='display: flex; flex-direction: column; gap: 2px;'>
                    <div style='display: flex; align-items: center; gap: 6px;'>
                        <div style='width: 8px; height: 8px; background: #ef4444; border-radius: 50%;'></div>
                        <span style='font-size: 11px; font-weight: 600; color: #ef4444; text-transform: uppercase; letter-spacing: 0.05em;'>Locked</span>
                    </div>
                    <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$locked}</div>
                </div>

                <div style='width: 1px; height: 24px; background: #e2e8f0;'></div>

                <div style='display: flex; flex-direction: column; gap: 2px;'>
                    <div style='display: flex; align-items: center; gap: 6px;'>
                        <div style='width: 8px; height: 8px; background: #f59e0b; border-radius: 50%;'></div>
                        <span style='font-size: 11px; font-weight: 600; color: #f59e0b; text-transform: uppercase; letter-spacing: 0.05em;'>Disabled</span>
                    </div>
                    <div style='padding-left: 14px; font-size: 15px; font-weight: 800; color: #1e293b;'>{$disabled}</div>
                </div>
            </div>
        </div>
    ");
}
}
