<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Services\PaymentService;
use App\Services\Payment\VnpayService;
use App\Services\Payment\MomoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private VnpayService $vnpayService,
        private MomoService $momoService
    ) {
    }

    /**
     * Get payment history
     */
    public function index(Request $request): JsonResponse
    {
        $payments = $this->paymentService->getList(
            $request->user(),
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Get payment details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $payment = $this->paymentService->getById($request->user(), $id);

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giao dịch'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Create VNPay payment
     */
    public function createVnpay(CreatePaymentRequest $request): JsonResponse
    {
        $result = $this->vnpayService->createPayment(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'payment_url' => $result['payment_url'],
                'transaction_id' => $result['transaction_id']
            ]
        ]);
    }

    /**
     * Handle VNPay callback
     */
    public function vnpayCallback(Request $request): \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $result = $this->vnpayService->handleCallback($request->all());

        // If called from frontend (AJAX), return JSON
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? '',
                'transaction_id' => $result['transaction_id'] ?? '',
            ]);
        }

        // Otherwise redirect (direct browser access)
        $redirectUrl = config('app.frontend_url') . '/dashboard/deposit/callback';
        $redirectUrl .= '?status=' . ($result['success'] ? 'success' : 'failed');
        $redirectUrl .= '&transaction_id=' . $result['transaction_id'];

        return redirect()->to($redirectUrl);
    }

    /**
     * Create Momo payment
     */
    public function createMomo(CreatePaymentRequest $request): JsonResponse
    {
        $result = $this->momoService->createPayment(
            $request->user(),
            $request->validated()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment_url' => $result['payment_url'],
                'transaction_id' => $result['transaction_id']
            ]
        ]);
    }

    /**
     * Handle Momo callback
     */
    public function momoCallback(Request $request): \Illuminate\Http\RedirectResponse
    {
        $result = $this->momoService->handleCallback($request->all());

        $redirectUrl = config('app.frontend_url') . '/deposit/result';
        $redirectUrl .= '?status=' . ($result['success'] ? 'success' : 'failed');
        $redirectUrl .= '&transaction_id=' . $result['transaction_id'];

        return redirect()->to($redirectUrl);
    }

    /**
     * Handle Momo IPN notify
     */
    public function momoNotify(Request $request): JsonResponse
    {
        $this->momoService->handleNotify($request->all());

        return response()->json(['success' => true]);
    }

    /**
     * Create bank transfer request
     */
    public function createBankTransfer(CreatePaymentRequest $request): JsonResponse
    {
        $result = $this->paymentService->createBankTransfer(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu nạp tiền đã được tạo. Vui lòng chuyển khoản theo thông tin bên dưới.',
            'data' => $result
        ]);
    }

    /**
     * Get bank transfer info
     */
    public function getBankInfo(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'bank_name' => config('payment.bank_name'),
                'account_number' => config('payment.bank_account_number'),
                'account_name' => config('payment.bank_account_name'),
                'branch' => config('payment.bank_branch'),
            ]
        ]);
    }
}
