<?php

namespace App\Http\Controllers;

use App\Services\ChatbotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(
        private ChatbotService $chatbotService,
    ) {
    }

    /**
     * Handle chatbot message from guest
     * 
     * POST /api/public/chatbot
     * Body: { "text": "Có đất nào ở Bình Phước không?", "guest_id": "abc123" }
     */
    public function handle(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:1000',
            'guest_id' => 'nullable|string|max:100',
        ]);

        $result = $this->chatbotService->handleMessage($request->input('text'));

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
