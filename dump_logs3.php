<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \App\Models\PayoutLog::whereIn('id', [19, 33])->get(['id', 'account_id', 'user_id', 'deleted_at', 'status', 'created_at']);
file_put_contents('dump_logs3.txt', print_r($logs->toArray(), true));
echo "Done\n";
