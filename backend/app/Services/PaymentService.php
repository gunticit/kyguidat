<?php

namespace App\Services;

use App\Models\User;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentService
{
    /**
     * Get list of payments
     */
    public function getList(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $user->payments();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['method'])) {
            $query->where('method', $filters['method']);
        }

        if (isset($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get payment by ID
     */
    public function getById(User $user, int $id): ?Payment
    {
        return $user->payments()->find($id);
    }

    /**
     * Create bank transfer request
     */
    public function createBankTransfer(User $user, array $data): array
    {
        $transactionId = $this->generateTransactionId();
        $amount = $data['amount'];

        $payment = $user->payments()->create([
            'transaction_id' => $transactionId,
            'method' => Payment::METHOD_BANK_TRANSFER,
            'amount' => $amount,
            'fee' => 0,
            'net_amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'expired_at' => now()->addHours(24),
        ]);

        return [
            'payment' => $payment,
            'bank_info' => [
                'bank_name' => config('payment.bank_name'),
                'account_number' => config('payment.bank_account_number'),
                'account_name' => config('payment.bank_account_name'),
                'branch' => config('payment.bank_branch'),
                'transfer_content' => $transactionId,
                'amount' => $amount,
            ],
        ];
    }

    /**
     * Process successful payment
     */
    public function processSuccessfulPayment(Payment $payment): void
    {
        $payment->update([
            'status' => Payment::STATUS_COMPLETED,
            'paid_at' => now(),
        ]);

        // Add money to wallet
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $payment->user_id],
            ['balance' => 0, 'frozen_balance' => 0]
        );

        $balanceBefore = $wallet->balance;
        $wallet->increment('balance', $payment->net_amount);

        // Create wallet transaction
        WalletTransaction::create([
            'wallet_id' => $wallet->id,
            'type' => WalletTransaction::TYPE_DEPOSIT,
            'amount' => $payment->net_amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $wallet->balance,
            'description' => 'Nạp tiền qua ' . strtoupper($payment->method),
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
        ]);
    }

    /**
     * Generate unique transaction ID
     */
    public function generateTransactionId(): string
    {
        $transactionId = 'TXN' . date('YmdHis') . strtoupper(Str::random(4));
        
        while (Payment::where('transaction_id', $transactionId)->exists()) {
            $transactionId = 'TXN' . date('YmdHis') . strtoupper(Str::random(4));
        }

        return $transactionId;
    }
}
