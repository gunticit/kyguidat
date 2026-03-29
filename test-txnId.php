<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$payments = App\Models\Payment::where('status', App\Models\Payment::STATUS_PENDING)->get();
foreach ($payments as $p) {
    $txnId = (string) $p->transaction_id;
    if (!empty($txnId)) {
        $upper = strtoupper($txnId);
        if ($upper === "") {
            echo "FOUND empty strtoupper!\n";
        }
    }
    echo "ID: " . $p->id . " | txnId: '" . $txnId . "' | empty: " . (empty($txnId)?"1":"0") . "\n";
}
