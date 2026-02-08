<?php

namespace App\Http\Controllers;

use App\Http\Requests\Support\StoreSupportRequest;
use App\Http\Requests\Support\UpdateSupportRequest;
use App\Http\Requests\Support\AddMessageRequest;
use App\Services\SupportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function __construct(
        private SupportService $supportService
    ) {}

    /**
     * Get list of support tickets
     */
    public function index(Request $request): JsonResponse
    {
        $tickets = $this->supportService->getList(
            $request->user(),
            $request->all()
        );

        return response()->json([
            'success' => true,
            'data' => $tickets
        ]);
    }

    /**
     * Create new support ticket
     */
    public function store(StoreSupportRequest $request): JsonResponse
    {
        $ticket = $this->supportService->create(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Yêu cầu hỗ trợ đã được gửi',
            'data' => $ticket
        ], 201);
    }

    /**
     * Get support ticket details
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $ticket = $this->supportService->getById($request->user(), $id);

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ticket
        ]);
    }

    /**
     * Update support ticket
     */
    public function update(UpdateSupportRequest $request, int $id): JsonResponse
    {
        $ticket = $this->supportService->update(
            $request->user(),
            $id,
            $request->validated()
        );

        if (!$ticket) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy yêu cầu hỗ trợ'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thành công',
            'data' => $ticket
        ]);
    }

    /**
     * Delete support ticket
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $result = $this->supportService->delete($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa yêu cầu hỗ trợ'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Xóa thành công'
        ]);
    }

    /**
     * Add message to support ticket
     */
    public function addMessage(AddMessageRequest $request, int $id): JsonResponse
    {
        $message = $this->supportService->addMessage(
            $request->user(),
            $id,
            $request->validated()
        );

        if (!$message) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể gửi tin nhắn'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tin nhắn đã được gửi',
            'data' => $message
        ]);
    }

    /**
     * Get messages for a support ticket
     */
    public function getMessages(Request $request, int $id): JsonResponse
    {
        $messages = $this->supportService->getMessages($request->user(), $id);

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**
     * Close support ticket
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $result = $this->supportService->close($request->user(), $id);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đóng yêu cầu hỗ trợ'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đã đóng yêu cầu hỗ trợ'
        ]);
    }
}
