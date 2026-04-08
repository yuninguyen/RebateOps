<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \App\Models\PayoutLog::withTrashed()->whereHas('account.email', function($q) {
    $q->where('email', 'shimshakbeckman01@hotmail.com');
})->get();

$out = "ID | Type | amount_usd | net_usd | vnd | parent | Del\n";
foreach($logs as $l) {
    $out .= sprintf("%d | %s | %.2f | %.2f | %.2f | %s | %s\n",
        $l->id, $l->transaction_type, $l->amount_usd, $l->net_amount_usd, $l->total_vnd,
        $l->parent_id ?? 'NULL', $l->deleted_at ?? 'NULL'
    );
}
file_put_contents('dump_logs.txt', $out);
echo "Done\n";
