<?php

namespace App\Services\Payment;

use App\Models\User;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Http;

class MomoService
{
    public function __construct(
        private PaymentService $paymentService
    ) {}

    /**
     * Create Momo payment
     */
    public function createPayment(User $user, array $data): array
    {
        $partnerCode = config('payment.momo.partner_code');
        $accessKey = config('payment.momo.access_key');
        $secretKey = config('payment.momo.secret_key');
        $endpoint = config('payment.momo.endpoint');
        $returnUrl = config('payment.momo.return_url');
        $notifyUrl = config('payment.momo.notify_url');

        $transactionId = $this->paymentService->generateTransactionId();
        $amount = (int) $data['amount'];
        $orderId = $transactionId;
        $requestId = $transactionId . '_' . time();
        $orderInfo = "Nạp tiền vào tài khoản - " . $transactionId;
        $requestType = "captureWallet";
        $extraData = "";

        // Create payment record
        $payment = $user->payments()->create([
            'transaction_id' => $transactionId,
            'method' => Payment::METHOD_MOMO,
            'amount' => $amount,
            'fee' => 0,
            'net_amount' => $amount,
            'status' => Payment::STATUS_PENDING,
            'expired_at' => now()->addMinutes(15),
        ]);

        // Create signature
        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData 
            . "&ipnUrl=" . $notifyUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo 
            . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $returnUrl 
            . "&requestId=" . $requestId . "&requestType=" . $requestType;
        
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $requestData = [
            'partnerCode' => $partnerCode,
            'partnerName' => config('app.name'),
            'storeId' => $partnerCode,
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $returnUrl,
            'ipnUrl' => $notifyUrl,
            'lang' => 'vi',
            'requestType' => $requestType,
            'extraData' => $extraData,
            'signature' => $signature,
        ];

        try {
            $response = Http::post($endpoint, $requestData);
            $result = $response->json();

            if (isset($result['payUrl'])) {
                return [
                    'success' => true,
                    'payment_url' => $result['payUrl'],
                    'transaction_id' => $transactionId,
                ];
            }

            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => $result,
            ]);

            return [
                'success' => false,
                'message' => $result['message'] ?? 'Không thể tạo thanh toán Momo',
                'transaction_id' => $transactionId,
            ];
        } catch (\Exception $e) {
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'gateway_response' => ['error' => $e->getMessage()],
            ]);

            return [
                'success' => false,
                'message' => 'Lỗi kết nối đến Momo',
                'transaction_id' => $transactionId,
            ];
        }
    }

    /**
     * Handle Momo callback
     */
    public function handleCallback(array $data): array
    {
        $orderId = $data['orderId'] ?? '';
        $transactionId = explode('_', $orderId)[0];
        $payment = Payment::where('transaction_id', $transactionId)->first();

        if (!$payment) {
            return [
                'success' => false,
                'transaction_id' => $transactionId,
                'message' => 'Không tìm thấy giao dịch',
            ];
        }

        $resultCode = $data['resultCode'] ?? -1;

        if ($resultCode == 0) {
            $payment->update([
                'gateway_transaction_id' => $data['transId'] ?? null,
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
            'message' => $data['message'] ?? 'Thanh toán thất bại',
        ];
    }

    /**
     * Handle Momo IPN notification
     */
    public function handleNotify(array $data): void
    {
        $this->handleCallback($data);
    }
}
