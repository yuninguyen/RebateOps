<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$p = App\Models\PayoutLog::find(19);
echo "Payment ID: " . ($p->user_payment_id ?? 'NULL') . "\n";
