<?php

namespace App\Filament\Resources\Traits;

trait HasPlatform
{
    public static function getPlatforms(): array
    {
        return \App\Models\Platform::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'slug')
            ->toArray();
    }
}
