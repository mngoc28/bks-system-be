<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PartnerChatController extends Controller
{
    protected ChatService $chatService;

    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    /**
     * Get all conversations for the partner
     */
    public function index(): JsonResponse
    {
        $partnerId = Auth::id();
        $conversations = $this->chatService->getUserConversations((int) $partnerId);
        return $this->successResponse($conversations, 'Conversations retrieved successfully');
    }

    /**
     * Get messages for a specific conversation
     */
    public function show(int $id): JsonResponse
    {
        $messages = $this->chatService->getMessages($id);
        return $this->successResponse($messages, 'Messages retrieved successfully');
    }

    /**
     * Send a message
     */
    public function store(Request $request): JsonResponse
    {
        $partnerId = Auth::id();
        $conversationId = $request->input('conversation_id');
        $content = $request->input('content');
        $metadata = $request->input('metadata');

        try {
            $message = $this->chatService->sendMessage(
                (int) $conversationId,
                (int) $partnerId,
                $content,
                $metadata
            );
            return $this->successResponse($message, 'Message sent successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send message', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }
}
