<?php
// app/Filament/Pages/Dashboard.php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int | string | array
    {
        return 2;
    }

    // 🟢 Dynamic welcome heading
    public function getHeading(): string
    {
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 18 => 'Good afternoon',
            default    => 'Good evening',
        };

        $name = auth()->user()?->name ?? 'there';

        return "{$greeting}, {$name} 👋";
    }

    public function getSubheading(): ?string
    {
        return 'Here\'s your operations overview for today.';
    }

    public function getWidgets(): array
    {
        return [
            // Row 0: Đưa thống kê Payout lên đầu trang (Full width nếu muốn)
            \App\Filament\Widgets\PayoutStats::class,
            
            // Row 1: Full width
            \App\Filament\Widgets\UserPlatformMatrixTable::class,

            // Row 2: 2 charts cạnh nhau
            \App\Filament\Widgets\EmailStatusChart::class,
            \App\Filament\Widgets\AccountPlatformChart::class,

            // Row 3: Full width
            \App\Filament\Widgets\AccountOverview::class,
        ];
    }
}