<?php

namespace App\Observers;

use App\Models\Email;
use App\Jobs\SyncGoogleSheetJob;

class EmailObserver
{
    // app/Observers/EmailObserver.php
    public function saved(Email $email): void
    {
        // Gọi Job đa năng để cập nhật dòng Email này lên Sheet
        \App\Jobs\SyncGoogleSheetJob::dispatch($email->id, \App\Models\Email::class);
    }

    public function deleted(Email $email): void
    {
        //
        \App\Jobs\SyncGoogleSheetJob::dispatch($email->id, \App\Models\Email::class, 'delete');
    }
}

// --- KẾT THÚC ---