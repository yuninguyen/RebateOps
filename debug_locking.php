<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\PayoutLog;
use App\Models\Email;

$emailAddr = 'shimshakbeckman01@hotmail.com';
echo "--- Debugging Logs for $emailAddr ---\n";

$email = Email::where('email', $emailAddr)->first();
if (!$email) {
    die("Email not found: $emailAddr\n");
}

$logs = PayoutLog::whereHas('account', fn($q) => $q->where('email_id', $email->id))
    ->withCount('children')
    ->withSum(['children as settled_children_sum' => fn($q) => $q->whereNotNull('user_payment_id')], 'amount_usd')
    ->get();

foreach ($logs as $l) {
    echo "ID: {$l->id} | Type: {$l->transaction_type} | Amount: {$l->net_amount_usd} | PaymentID: " . ($l->user_payment_id ?? 'NULL') . "\n";
    echo "  -> Count: {$l->children_count} | Settled Children Sum: " . ($l->settled_children_sum ?? '0') . "\n";
    
    $isLocked = $l->user_payment_id !== null || (floatval($l->settled_children_sum) >= floatval($l->net_amount_usd) && $l->children_count > 0);
    echo "  -> Evaluated Lock Status: " . ($isLocked ? "LOCKED 🔒" : "OPEN 🔓") . "\n";
    
    if ($l->children_count > 0) {
        foreach ($l->children as $child) {
            echo "     - Child ID: {$child->id} | Amount: {$child->amount_usd} | PaymentID: " . ($child->user_payment_id ?? 'NULL') . "\n";
        }
    }
}
