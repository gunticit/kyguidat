<?php

namespace App\Services\Payment;

use App\Models\User;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Str;

class VnpayService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Create VNPay payment URL
     */
    public function createPayment(User $user, array $data): array
    {
        $vnpTmnCode = config('payment.vnpay.tmn_code');
        $vnpHashSecret = config('payment.vnpay.hash_secret');
        $vnpUrl = config('payment.vnpay.url');
        $vnpReturnUrl = config('payment.vnpay.return_url');

        $transactionId = $this->paymentService->generateTransactionId();
        $amount = $data['amount'];

        // Create payment record
        $payment = $user->payments()->create([
            'transaction_id' => $transactionId,
            'method' => Payment::METHOD_VNPAY,
            'amount' => $amount,
            'fee' => 0,
            'net_amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'expired_at' => now()->addMinutes(15),
        ]);

        // Build VNPay request
        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnpTmnCode,
            "vnp_Amount" => $amount * 100,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => request()->ip(),
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => "Nạp tiền vào tài khoản - " . $transactionId,
            "vnp_OrderType" => "other",
            "vnp_ReturnUrl" => $vnpReturnUrl,
            "vnp_TxnRef" => $transactionId,
            "vnp_ExpireDate" => date('YmdHis', strtotime('+15 minutes')),
        ];

        ksort($inputData);
        $query = http_build_query($inputData);
        $hashData = $query;
        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);
        $vnpUrl .= "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;

        return [
            'payment_url' => $vnpUrl,
            'transaction_id' => $transactionId,
        ];
    }

    /**
     * Handle VNPay callback
     */
    public function handleCallback(array $data): array
    {
        $vnpHashSecret = config('payment.vnpay.hash_secret');
        $vnpSecureHash = $data['vnp_SecureHash'] ?? '';
        
        unset($data['vnp_SecureHash']);
        unset($data['vnp_SecureHashType']);
        
        ksort($data);
        $hashData = http_build_query($data);
        $secureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        $transactionId = $data['vnp_TxnRef'] ?? '';
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return [
                'success' => false,
                'transaction_id' => $transactionId,
                'message' => 'Không tìm thấy giao dịch',
            ];
        }

        if ($secureHash !== $vnpSecureHash) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => $data,
            ]);

            return [
                'success' => false,
                'transaction_id' => $transactionId,
                'message' => 'Chữ ký không hợp lệ',
            ];
        }

        $responseCode = $data['vnp_ResponseCode'] ?? '';

        if ($responseCode === '00') {
            $payment->update([
                'gateway_transaction_id' => $data['vnp_TransactionNo'] ?? null,
                'gateway_response' => $data,
            ]);

            $this->paymentService->processSuccessfulPayment($payment);

            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'message' => 'Thanh toán thành công',
            ];
        }

        $payment->update([
            'status' => Payment::STATUS_FAILED,
            'gateway_response' => $data,
        ]);

        return [
            'success' => false,
            'transaction_id' => $transactionId,
            'message' => 'Thanh toán thất bại',
        ];
    }
}
