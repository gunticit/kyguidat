<?php

namespace App\Http\Controllers;

use App\Models\IpnConfiguration;
use App\Models\IpnLog;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IpnHandlerController extends Controller
{
    /**
     * Handle VNPay IPN
     */
    public function vnpay(Request $request): JsonResponse
    {
        return $this->handleIpn('vnpay', $request);
    }

    /**
     * Handle Momo IPN
     */
    public function momo(Request $request): JsonResponse
    {
        return $this->handleIpn('momo', $request);
    }

    /**
     * Handle ZaloPay IPN
     */
    public function zalopay(Request $request): JsonResponse
    {
        return $this->handleIpn('zalopay', $request);
    }

    /**
     * Handle Bank Transfer IPN
     */
    public function bank(Request $request): JsonResponse
    {
        return $this->handleIpn('bank', $request);
    }

    /**
     * Handle Custom IPN
     */
    public function custom(Request $request): JsonResponse
    {
        return $this->handleIpn('custom', $request);
    }

    /**
     * Generic IPN handler
     */
    private function handleIpn(string $provider, Request $request): JsonResponse
    {
        $data = $request->all();
        $ipAddress = $request->ip();

        // Log the incoming request
        Log::info("IPN received from {$provider}", [
            'ip' => $ipAddress,
            'data' => $data,
        ]);

        // Find active configuration for this provider
        $configuration = IpnConfiguration::active()
            ->byProvider($provider)
            ->first();

        try {
            // Extract transaction info based on provider
            $transactionInfo = $this->extractTransactionInfo($provider, $data);

            // Validate signature if secret key is configured
            if ($configuration && $configuration->secret_key) {
                $isValid = $this->validateSignature($provider, $data, $configuration->secret_key);
                if (!$isValid) {
                    throw new \Exception('Invalid signature');
                }
            }

            // Process the payment
            $result = $this->processPayment($provider, $transactionInfo, $data);

            // Log success
            IpnLog::createLog([
                'ipn_configuration_id' => $configuration?->id,
                'provider' => $provider,
                'transaction_id' => $transactionInfo['transaction_id'] ?? null,
                'order_id' => $transactionInfo['order_id'] ?? null,
                'amount' => $transactionInfo['amount'] ?? null,
                'status' => IpnLog::STATUS_SUCCESS,
                'response_code' => $transactionInfo['response_code'] ?? '00',
                'request_data' => $data,
                'response_data' => $result,
                'ip_address' => $ipAddress,
            ]);

            // Update configuration trigger count
            $configuration?->recordTrigger();

            return $this->formatSuccessResponse($provider);

        } catch (\Exception $e) {
            Log::error("IPN processing failed for {$provider}", [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            // Log failure
            IpnLog::createLog([
                'ipn_configuration_id' => $configuration?->id,
                'provider' => $provider,
                'transaction_id' => $transactionInfo['transaction_id'] ?? null,
                'status' => IpnLog::STATUS_FAILED,
                'request_data' => $data,
                'ip_address' => $ipAddress,
                'error_message' => $e->getMessage(),
            ]);

            return $this->formatErrorResponse($provider, $e->getMessage());
        }
    }

    /**
     * Extract transaction info from request data
     */
    private function extractTransactionInfo(string $provider, array $data): array
    {
        switch ($provider) {
            case 'vnpay':
                return [
                    'transaction_id' => $data['vnp_TxnRef'] ?? null,
                    'order_id' => $data['vnp_TxnRef'] ?? null,
                    'amount' => isset($data['vnp_Amount']) ? (int)$data['vnp_Amount'] / 100 : null,
                    'response_code' => $data['vnp_ResponseCode'] ?? null,
                    'is_success' => ($data['vnp_ResponseCode'] ?? '') === '00' && ($data['vnp_TransactionStatus'] ?? '') === '00',
                    'bank_code' => $data['vnp_BankCode'] ?? null,
                    'card_type' => $data['vnp_CardType'] ?? null,
                    'pay_date' => $data['vnp_PayDate'] ?? null,
                ];

            case 'momo':
                return [
                    'transaction_id' => $data['transId'] ?? $data['requestId'] ?? null,
                    'order_id' => $data['orderId'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'response_code' => (string)($data['resultCode'] ?? ''),
                    'is_success' => ($data['resultCode'] ?? -1) == 0,
                    'message' => $data['message'] ?? null,
                ];

            case 'zalopay':
                return [
                    'transaction_id' => $data['zp_trans_id'] ?? null,
                    'order_id' => $data['app_trans_id'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'response_code' => (string)($data['status'] ?? ''),
                    'is_success' => ($data['status'] ?? 0) == 1,
                ];

            case 'bank':
            case 'custom':
            default:
                return [
                    'transaction_id' => $data['transaction_id'] ?? $data['txn_id'] ?? null,
                    'order_id' => $data['order_id'] ?? null,
                    'amount' => $data['amount'] ?? null,
                    'response_code' => $data['response_code'] ?? $data['code'] ?? null,
                    'is_success' => in_array(strtolower($data['status'] ?? ''), ['success', 'completed', '1', 'true']),
                ];
        }
    }

    /**
     * Validate signature based on provider
     */
    private function validateSignature(string $provider, array $data, string $secretKey): bool
    {
        switch ($provider) {
            case 'vnpay':
                // VNPay signature validation
                $vnpSecureHash = $data['vnp_SecureHash'] ?? '';
                unset($data['vnp_SecureHash'], $data['vnp_SecureHashType']);
                ksort($data);
                
                $hashData = '';
                foreach ($data as $key => $value) {
                    if (str_starts_with($key, 'vnp_') && $value != '') {
                        $hashData .= ($hashData ? '&' : '') . urlencode($key) . '=' . urlencode($value);
                    }
                }
                
                $calculatedHash = hash_hmac('sha512', $hashData, $secretKey);
                return hash_equals($vnpSecureHash, $calculatedHash);

            case 'momo':
                // Momo signature validation
                $receivedSignature = $data['signature'] ?? '';
                unset($data['signature']);
                
                $rawHash = "accessKey={$data['accessKey']}&amount={$data['amount']}&extraData={$data['extraData']}&message={$data['message']}&orderId={$data['orderId']}&orderInfo={$data['orderInfo']}&orderType={$data['orderType']}&partnerCode={$data['partnerCode']}&payType={$data['payType']}&requestId={$data['requestId']}&responseTime={$data['responseTime']}&resultCode={$data['resultCode']}&transId={$data['transId']}";
                
                $calculatedSignature = hash_hmac('sha256', $rawHash, $secretKey);
                return hash_equals($receivedSignature, $calculatedSignature);

            default:
                // Generic HMAC-SHA256 validation
                $receivedSignature = $data['signature'] ?? $data['X-Signature'] ?? '';
                unset($data['signature']);
                
                $calculatedSignature = hash_hmac('sha256', json_encode($data), $secretKey);
                return hash_equals($receivedSignature, $calculatedSignature);
        }
    }

    /**
     * Process payment and update wallet
     */
    private function processPayment(string $provider, array $transactionInfo, array $rawData): array
    {
        if (!$transactionInfo['is_success']) {
            return [
                'processed' => false,
                'reason' => 'Transaction not successful',
            ];
        }

        $transactionId = $transactionInfo['transaction_id'];
        $amount = $transactionInfo['amount'];

        if (!$transactionId) {
            return [
                'processed' => false,
                'reason' => 'Missing transaction ID',
            ];
        }

        // Find pending payment
        $payment = Payment::where('transaction_id', $transactionId)
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            // Check if already processed
            $existingPayment = Payment::where('transaction_id', $transactionId)
                ->where('status', 'completed')
                ->first();

            if ($existingPayment) {
                return [
                    'processed' => false,
                    'reason' => 'Already processed',
                ];
            }

            return [
                'processed' => false,
                'reason' => 'Payment not found',
            ];
        }

        // Process in transaction
        DB::beginTransaction();
        try {
            // Update payment status
            $payment->update([
                'status' => 'completed',
                'completed_at' => now(),
                'gateway_response' => $rawData,
            ]);

            // Add to user wallet
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $payment->user_id],
                ['balance' => 0]
            );

            $wallet->increment('balance', $amount);

            // Create wallet transaction
            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'deposit',
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'description' => "Nạp tiền qua {$provider} - {$transactionId}",
                'reference_type' => Payment::class,
                'reference_id' => $payment->id,
            ]);

            DB::commit();

            return [
                'processed' => true,
                'payment_id' => $payment->id,
                'amount' => $amount,
                'new_balance' => $wallet->balance,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Format success response based on provider requirements
     */
    private function formatSuccessResponse(string $provider): JsonResponse
    {
        switch ($provider) {
            case 'vnpay':
                return response()->json([
                    'RspCode' => '00',
                    'Message' => 'Confirm Success'
                ]);

            case 'momo':
                return response()->json([
                    'resultCode' => 0,
                    'message' => 'success'
                ]);

            case 'zalopay':
                return response()->json([
                    'return_code' => 1,
                    'return_message' => 'success'
                ]);

            default:
                return response()->json([
                    'success' => true,
                    'message' => 'IPN processed successfully'
                ]);
        }
    }

    /**
     * Format error response based on provider requirements
     */
    private function formatErrorResponse(string $provider, string $message): JsonResponse
    {
        switch ($provider) {
            case 'vnpay':
                return response()->json([
                    'RspCode' => '99',
                    'Message' => $message
                ], 200); // VNPay expects 200 even for errors

            case 'momo':
                return response()->json([
                    'resultCode' => -1,
                    'message' => $message
                ], 200);

            case 'zalopay':
                return response()->json([
                    'return_code' => 0,
                    'return_message' => $message
                ], 200);

            default:
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 400);
        }
    }
}
