<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::enableQueryLog();

$query = \App\Models\PayoutLog::query();
$query->whereDate('created_at', '2026-04-08');
// Emulate grouping
$query->where('account_id', 6)->whereNull('gc_brand');

// Emulate user's summary
$sum = $query->clone()->whereNull('payout_logs.parent_id')->sum('net_amount_usd');

print_r(DB::getQueryLog());
echo "SUM: " . $sum . "\n";
