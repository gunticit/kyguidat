<?php

namespace App\Http\Controllers;

use App\Http\Requests\Consignment\StoreConsignmentRequest;
use App\Http\Requests\Consignment\UpdateConsignmentRequest;
use App\Models\Consignment;
use App\Services\ConsignmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConsignmentController extends Controller
{
    public function __construct(
        private ConsignmentService $consignmentService
    ) {
    }

    /**
     * Get list of consignments
     */
    public function index(Request $request): JsonResponse
    {
        $consignments = $this->consignmentService->getList(
            $request->user(),
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $consignments
        ]);
    }

    /**
     * Create new consignment
     */
    public function store(StoreConsignmentRequest $request): JsonResponse
    {
        try {
            $consignment = $this->consignmentService->create(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Tạo yêu cầu ký gửi thành công',
                'data' => $consignment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    /**
     * Get consignment details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $consignment = $this->consignmentService->getById($request->user(), $id);

        if (!$consignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu ký gửi'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $consignment
        ]);
    }

    /**
     * Update consignment
     */
    public function update(UpdateConsignmentRequest $request, int $id): JsonResponse
    {
        $consignment = $this->consignmentService->update(
            $request->user(),
            $id,
            $request->validated()
        );

        if (!$consignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu ký gửi hoặc không thể cập nhật'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công',
            'data' => $consignment
        ]);
    }

    /**
     * Delete consignment
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $result = $this->consignmentService->delete($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa yêu cầu ký gửi'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Xóa thành công'
        ]);
    }

    /**
     * Cancel consignment
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $result = $this->consignmentService->cancel($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể hủy yêu cầu ký gửi'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã hủy yêu cầu ký gửi'
        ]);
    }

    /**
     * Get consignment history
     */
    public function history(Request $request, int $id): JsonResponse
    {
        $history = $this->consignmentService->getHistory($request->user(), $id);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    /**
     * Update price of an approved/selling consignment
     */
    public function updatePrice(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $consignment = $this->consignmentService->updatePrice(
            $request->user(),
            $id,
            $request->input('price')
        );

        if (!$consignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy hoặc không thể cập nhật giá'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật giá thành công',
            'data' => $consignment
        ]);
    }

    /**
     * Reactivate a deactivated consignment
     */
    public function reactivate(Request $request, int $id): JsonResponse
    {
        $consignment = $this->consignmentService->reactivate($request->user(), $id);

        if (!$consignment) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể mở lại sản phẩm này'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã mở lại sản phẩm thành công',
            'data' => $consignment
        ]);
    }

    /**
     * Get user's posting quota
     */
    public function postingQuota(Request $request): JsonResponse
    {
        $quota = $this->consignmentService->getPostingQuota($request->user());

        return response()->json([
            'success' => true,
            'data' => $quota
        ]);
    }
}
