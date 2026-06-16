<?php

declare(strict_types=1);

namespace App\Http\Controllers\Stay;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\SendChatMessageRequest;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class StayChatController extends Controller
{
    public function __construct(
        private readonly ChatService $chatService,
    ) {
    }

    public function index(): JsonResponse
    {
        $userId = (int) Auth::id();
        $conversations = $this->chatService->getConversationsForParticipant($userId);

        return $this->successResponse($conversations, 'Conversations retrieved successfully');
    }

    public function show(int $id): JsonResponse
    {
        try {
            $userId = (int) Auth::id();
            $messages = $this->chatService->getMessagesForParticipant($id, $userId);

            return $this->successResponse($messages, 'Messages retrieved successfully');
        } catch (HttpExceptionInterface $e) {
            return $this->errorResponse($e->getMessage(), null, $this->mapHttpStatus($e->getStatusCode()));
        }
    }

    public function store(SendChatMessageRequest $request): JsonResponse
    {
        try {
            $userId = (int) Auth::id();
            $message = $this->chatService->sendMessage(
                (int) $request->input('conversation_id'),
                $userId,
                (string) $request->input('content'),
                $request->input('metadata'),
            );

            return $this->successResponse($message, 'Message sent successfully');
        } catch (HttpExceptionInterface $e) {
            return $this->errorResponse($e->getMessage(), null, $this->mapHttpStatus($e->getStatusCode()));
        } catch (\Throwable) {
            return $this->errorResponse('Failed to send message', null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    private function mapHttpStatus(int $statusCode): HttpStatus
    {
        return HttpStatus::tryFrom($statusCode) ?? HttpStatus::BAD_REQUEST;
    }
}
