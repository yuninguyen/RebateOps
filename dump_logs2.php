<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$logs = \App\Models\PayoutLog::withTrashed()->whereHas('account.email', function($q) {
    $q->where('email', 'shimshakbeckman01@hotmail.com');
})->get();

$out = "ID | Type | brand | deleted\n";
foreach($logs as $l) {
    $out .= sprintf("%d | %s | %s | %s\n",
        $l->id, $l->transaction_type, $l->gc_brand ?? 'NULL', $l->deleted_at ?? 'NULL'
    );
}
file_put_contents('dump_logs2.txt', $out);
echo "Done\n";
