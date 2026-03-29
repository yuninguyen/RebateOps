<?php

namespace App\Observers;

use App\Models\RebateTracker;
use App\Jobs\SyncGoogleSheetJob;

class RebateTrackerObserver
{
    // FIX #7: Xóa inject GoogleSheetService (không dùng, tốn khởi tạo Google API client)
    /**
    public function created(RebateTracker $tracker): void
    {
        SyncGoogleSheetJob::dispatch($tracker->id, get_class($tracker));
    }

    public function updated(RebateTracker $tracker): void
    {
        SyncGoogleSheetJob::dispatch($tracker->id, get_class($tracker));
    }
    */

    public function saved(RebateTracker $tracker): void
    {
        SyncGoogleSheetJob::dispatch($tracker->id, get_class($tracker));
    }

    public function deleted(RebateTracker $tracker): void
    {
        // FIX #6: truyền platform để Job biết xóa đúng tab platform_Tracker
        $platform = $tracker->account?->platform ?: null;
        SyncGoogleSheetJob::dispatch($tracker->id, get_class($tracker), 'delete', $platform);
    }
}
