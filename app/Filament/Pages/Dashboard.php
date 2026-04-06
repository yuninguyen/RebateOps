<?php
// app/Filament/Pages/Dashboard.php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getColumns(): int|string|array
    {
        return 2;
    }

    // 🟢 Dynamic welcome heading
    public function getHeading(): string
    {
        $hour = (int) now()->format('H');
        $greeting = match (true) {
            $hour < 12 => __('system.greetings.morning'),
            $hour < 18 => __('system.greetings.afternoon'),
            default => __('system.greetings.evening'),
        };

        $name = auth()->user()?->name ?? __('system.n/a');

        return "{$greeting}, {$name} 👋";
    }

    public function getSubheading(): ?string
    {
        return __('system.greetings.operations_overview');
    }

    public function getWidgets(): array
    {
        return [
            // Row 0: Summary Payout Stats
            \App\Filament\Widgets\PayoutStats::class,

            // Row 2: Revenue Report Matrix
            \App\Filament\Widgets\UserPlatformMatrixTable::class,

            // Row 3: Payroll Table (Down to Bottom)
            \App\Filament\Widgets\AdminUserEarningsTable::class,

            // Row 4: Operations Charts (New Top Priority)
            \App\Filament\Widgets\EmailStatusChart::class,
            \App\Filament\Widgets\AccountPlatformChart::class,

            // Row 5: Account Details
            \App\Filament\Widgets\AccountOverview::class,


        ];
    }
}
