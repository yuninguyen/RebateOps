<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$emails = ['shimshakbeckman01@hotmail.com', 'duranccoffey45383@gmail.com'];
foreach ($emails as $email) {
    echo "--- Findings for $email ---\n";
    $logs = \App\Models\PayoutLog::whereHas('account', fn($q) => $q->where('email', $email))->get();
    foreach ($logs as $l) {
        $childrenPaymentStatus = $l->children->pluck('user_payment_id')->filter()->count() . "/" . $l->children->count();
        echo "ID: {$l->id} | Type: {$l->transaction_type} | Amount: {$l->net_amount_usd} | Parent: {$l->parent_id} | PaymentID: {$l->user_payment_id} | ChildrenSettled: $childrenPaymentStatus\n";
    }
}
