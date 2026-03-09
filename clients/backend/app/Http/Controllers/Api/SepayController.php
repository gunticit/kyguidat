<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;

class SepayController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function webhook(Request $request)
    {
        $sepayApiKey = env('SEPAY_API_KEY', 'ANlxGJkKFDoB6uy5BEGjfTjsbUEJPOxu6MBvuEjklS4=');

        // Verify Authorization header
        $authHeader = $request->header('Authorization');
        if (!$authHeader || (!str_contains($authHeader, $sepayApiKey))) {
            Log::warning('Sepay Webhook: Unauthorized', ['header' => $authHeader]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $request->all();

        Log::info('Sepay Webhook Received:', $data);

        // Sepay payload
        $transferAmount = $data['transferAmount'] ?? 0;
        $transferType = $data['transferType'] ?? '';
        $content = strtoupper($data['content'] ?? '');
        $transactionId = $data['id'] ?? '';
        $referenceCode = $data['referenceCode'] ?? '';

        if ($transferType !== 'in' || $transferAmount <= 0) {
            return response()->json(['success' => true, 'message' => 'Ignored non-incoming transfer']);
        }

        // Idempotency Check (Chống trùng lặp giao dịch)
        $txId = $referenceCode ?: ('SEPAY_' . $transactionId);
        $existingPayment = Payment::where('transaction_id', $txId)->first();
        if ($existingPayment && $existingPayment->status === Payment::STATUS_COMPLETED) {
            Log::info('Sepay Webhook: Transaction already processed', ['txId' => $txId]);
            return response()->json(['success' => true, 'message' => 'Transaction already processed']);
        }

        // Match pending transaction by transaction_id in content
        $payment = clone $existingPayment; // Or we just do a fresh query
        $payment = Payment::where('status', Payment::STATUS_PENDING)
            ->get()
            ->filter(function ($p) use ($content) {
                $txnId = (string) ($p->transaction_id ?? '');
                if (empty($txnId))
                    return false;
                return str_contains($content, strtoupper($txnId));
            })
            ->first();

        if ($payment) {
            if ($transferAmount >= $payment->net_amount) {
                $this->paymentService->processSuccessfulPayment($payment);
                $payment->update([
                    'transaction_id' => $referenceCode ?: $payment->transaction_id,
                    'method' => 'sepay'
                ]);
                return response()->json(['success' => true, 'message' => 'Payment processed by TXN ID']);
            } else {
                return response()->json(['success' => true, 'message' => 'Ignored due to insufficient amount']);
            }
        }

        // Alternative fallback: find the user by phone number in content
        $phone = null;

        // The expected format is "KHODAT {phone_number}"
        // We match this specifically so we don't accidentally match the sender's phone number added by the bank
        if (preg_match('/KHODAT\s*(0[3|5|7|8|9][0-9]{8})\b/i', $content, $matches)) {
            $phone = $matches[1];
        } else if (preg_match_all('/(0[3|5|7|8|9][0-9]{8})\b/', $content, $matches)) {
            // Fallback: If "KHODAT" is missing, check all phone numbers and use the first one that belongs to a user
            foreach ($matches[1] as $p) {
                if (User::where('phone', $p)->exists()) {
                    $phone = $p;
                    break;
                }
            }
        }

        if ($phone) {
            $user = User::where('phone', $phone)->first();
            if ($user) {
                // Try to find the pending payment for this user
                $payment = Payment::where('user_id', $user->id)
                    ->where('status', Payment::STATUS_PENDING)
                    ->where('net_amount', '<=', $transferAmount)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($payment) {
                    $payment->update([
                        'transaction_id' => $referenceCode ?: ('SEPAY_' . $transactionId),
                        'method' => 'sepay'
                    ]);
                    $this->paymentService->processSuccessfulPayment($payment);
                    return response()->json(['success' => true, 'message' => 'Payment processed by Phone (Matched Pending)']);
                } else {
                    // Create a new payment if no pending one is found
                    $payment = $user->payments()->create([
                        'transaction_id' => $referenceCode ?: ('SEPAY_' . $transactionId),
                        'method' => 'sepay',
                        'amount' => $transferAmount,
                        'fee' => 0,
                        'net_amount' => $transferAmount,
                        'status' => Payment::STATUS_PENDING,
                    ]);

                    $this->paymentService->processSuccessfulPayment($payment);
                    return response()->json(['success' => true, 'message' => 'Payment processed by Phone (Created New)']);
                }
            }
        }

        Log::info('Sepay Webhook: Unmatched transaction', $data);
        return response()->json(['success' => true, 'message' => 'Unmatched transaction recorded']);
    }
}
