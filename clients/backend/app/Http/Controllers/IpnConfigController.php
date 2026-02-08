<?php

namespace App\Http\Controllers;

use App\Models\IpnConfiguration;
use App\Models\IpnLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class IpnConfigController extends Controller
{
    /**
     * Get all IPN configurations
     */
    public function index(Request $request): JsonResponse
    {
        $query = IpnConfiguration::with('creator:id,name');

        // Filter by provider
        if ($request->has('provider')) {
            $query->byProvider($request->provider);
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
        }

        $configurations = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $configurations,
            'meta' => [
                'providers' => IpnConfiguration::PROVIDERS,
            ]
        ]);
    }

    /**
     * Create new IPN configuration
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'provider' => 'required|string|in:' . implode(',', array_keys(IpnConfiguration::PROVIDERS)),
            'ipn_url' => 'required|string|max:500',
            'secret_key' => 'nullable|string|max:255',
            'merchant_id' => 'nullable|string|max:100',
            'additional_config' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $configuration = IpnConfiguration::create([
            ...$validator->validated(),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tạo cấu hình IPN thành công',
            'data' => $configuration
        ], 201);
    }

    /**
     * Get single IPN configuration
     */
    public function show(int $id): JsonResponse
    {
        $configuration = IpnConfiguration::with(['creator:id,name', 'logs' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->find($id);

        if (!$configuration) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy cấu hình IPN'
            ], 404);
        }

        // Show secret key for detail view (base64 encoded for security)
        $configuration->makeVisible('secret_key');

        return response()->json([
            'success' => true,
            'data' => $configuration
        ]);
    }

    /**
     * Update IPN configuration
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $configuration = IpnConfiguration::find($id);

        if (!$configuration) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy cấu hình IPN'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'provider' => 'sometimes|string|in:' . implode(',', array_keys(IpnConfiguration::PROVIDERS)),
            'ipn_url' => 'sometimes|string|max:500',
            'secret_key' => 'nullable|string|max:255',
            'merchant_id' => 'nullable|string|max:100',
            'additional_config' => 'nullable|array',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $configuration->update($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật cấu hình IPN thành công',
            'data' => $configuration
        ]);
    }

    /**
     * Delete IPN configuration
     */
    public function destroy(int $id): JsonResponse
    {
        $configuration = IpnConfiguration::find($id);

        if (!$configuration) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy cấu hình IPN'
            ], 404);
        }

        $configuration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa cấu hình IPN thành công'
        ]);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): JsonResponse
    {
        $configuration = IpnConfiguration::find($id);

        if (!$configuration) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy cấu hình IPN'
            ], 404);
        }

        $configuration->update([
            'is_active' => !$configuration->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => $configuration->is_active ? 'Đã kích hoạt' : 'Đã tắt',
            'data' => $configuration
        ]);
    }

    /**
     * Test IPN URL
     */
    public function test(Request $request, int $id): JsonResponse
    {
        $configuration = IpnConfiguration::find($id);

        if (!$configuration) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy cấu hình IPN'
            ], 404);
        }

        // Create test payload based on provider
        $testPayload = $this->generateTestPayload($configuration->provider);

        try {
            $client = new \GuzzleHttp\Client(['timeout' => 10]);
            
            $response = $client->post($configuration->full_ipn_url, [
                'json' => $testPayload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-IPN-Test' => 'true',
                    'X-IPN-Provider' => $configuration->provider,
                ]
            ]);

            // Log the test
            IpnLog::createLog([
                'ipn_configuration_id' => $configuration->id,
                'provider' => $configuration->provider,
                'transaction_id' => 'TEST_' . time(),
                'status' => IpnLog::STATUS_SUCCESS,
                'request_data' => $testPayload,
                'response_data' => [
                    'status_code' => $response->getStatusCode(),
                    'body' => json_decode($response->getBody()->getContents(), true),
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test IPN URL thành công',
                'data' => [
                    'status_code' => $response->getStatusCode(),
                    'response' => json_decode($response->getBody()->getContents(), true),
                ]
            ]);

        } catch (\Exception $e) {
            // Log the failure
            IpnLog::createLog([
                'ipn_configuration_id' => $configuration->id,
                'provider' => $configuration->provider,
                'transaction_id' => 'TEST_' . time(),
                'status' => IpnLog::STATUS_FAILED,
                'request_data' => $testPayload,
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test IPN URL thất bại',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get IPN logs
     */
    public function logs(Request $request): JsonResponse
    {
        $query = IpnLog::with('configuration:id,name,provider');

        // Filter by configuration
        if ($request->has('configuration_id')) {
            $query->where('ipn_configuration_id', $request->configuration_id);
        }

        // Filter by provider
        if ($request->has('provider')) {
            $query->byProvider($request->provider);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        // Date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    }

    /**
     * Get log detail
     */
    public function logDetail(int $id): JsonResponse
    {
        $log = IpnLog::with('configuration:id,name,provider')->find($id);

        if (!$log) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy log'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $log
        ]);
    }

    /**
     * Get IPN statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $query = IpnLog::query();

        // Date range
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Count by status
        $statusCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Count by provider
        $providerCounts = (clone $query)
            ->selectRaw('provider, COUNT(*) as count')
            ->groupBy('provider')
            ->pluck('count', 'provider');

        // Total amount
        $totalAmount = (clone $query)
            ->where('status', IpnLog::STATUS_SUCCESS)
            ->sum('amount');

        // Recent activity (last 7 days)
        $recentActivity = IpnLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date');

        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => $query->count(),
                'by_status' => $statusCounts,
                'by_provider' => $providerCounts,
                'total_success_amount' => $totalAmount,
                'recent_activity' => $recentActivity,
                'active_configurations' => IpnConfiguration::active()->count(),
            ]
        ]);
    }

    /**
     * Generate URL endpoints info
     */
    public function endpoints(): JsonResponse
    {
        $baseUrl = config('app.url');

        return response()->json([
            'success' => true,
            'data' => [
                'base_url' => $baseUrl,
                'endpoints' => [
                    'vnpay' => [
                        'callback' => $baseUrl . '/api/payments/vnpay/callback',
                        'ipn' => $baseUrl . '/api/ipn/vnpay',
                    ],
                    'momo' => [
                        'callback' => $baseUrl . '/api/payments/momo/callback',
                        'notify' => $baseUrl . '/api/payments/momo/notify',
                        'ipn' => $baseUrl . '/api/ipn/momo',
                    ],
                    'bank' => [
                        'ipn' => $baseUrl . '/api/ipn/bank',
                    ],
                    'custom' => [
                        'ipn' => $baseUrl . '/api/ipn/custom',
                    ],
                ],
                'webhook_format' => [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-Signature' => 'HMAC-SHA256 signature (optional)',
                    ],
                    'body' => [
                        'transaction_id' => 'string (required)',
                        'order_id' => 'string',
                        'amount' => 'number',
                        'status' => 'string (success|failed|pending)',
                        'message' => 'string',
                        'timestamp' => 'ISO 8601 datetime',
                    ]
                ]
            ]
        ]);
    }

    /**
     * Generate test payload based on provider
     */
    private function generateTestPayload(string $provider): array
    {
        $base = [
            'transaction_id' => 'TEST_' . uniqid(),
            'order_id' => 'ORDER_TEST_' . time(),
            'amount' => 100000,
            'status' => 'success',
            'message' => 'Test IPN from system',
            'timestamp' => now()->toIso8601String(),
            'is_test' => true,
        ];

        switch ($provider) {
            case 'vnpay':
                return [
                    ...$base,
                    'vnp_TxnRef' => $base['transaction_id'],
                    'vnp_Amount' => $base['amount'] * 100,
                    'vnp_ResponseCode' => '00',
                    'vnp_TransactionStatus' => '00',
                ];
            
            case 'momo':
                return [
                    ...$base,
                    'partnerCode' => 'TEST',
                    'orderId' => $base['order_id'],
                    'requestId' => $base['transaction_id'],
                    'resultCode' => 0,
                    'message' => 'Successful.',
                ];

            case 'zalopay':
                return [
                    ...$base,
                    'app_id' => 'TEST',
                    'app_trans_id' => $base['transaction_id'],
                    'zp_trans_id' => 'ZP_' . time(),
                    'status' => 1,
                ];

            default:
                return $base;
        }
    }
}
