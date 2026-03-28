<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Services\ChatbotService;
use App\Http\Validations\ChatbotValidation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class ChatbotController extends Controller
{
    public function __construct(
        private readonly ChatbotService $chatbotService,
        private readonly ChatbotValidation $chatbotValidation
    ) {
    }

    /**
     * Retrieve chatbot flow data.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->listValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->chatbotService->getChatbotQuestionsList($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Retrieve chatbot question flow list using default request context.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listQuestionFlow(Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->listQuestionFlowValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->chatbotService->listQuestionFlows($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Retrieve the first chatbot question for public flow.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function startQuestion(Request $request): JsonResponse
    {
        $result = $this->chatbotService->getStartQuestion($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Retrieve next chatbot question by identifier.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function nextQuestion(int $id, Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->nextQuestionValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->chatbotService->getNextQuestion($id, $request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Retrieve chatbot question detail by identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->chatbotValidation->detailValidation($id);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->chatbotService->getChatbotQuestionDetail($id);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Create a new chatbot question.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->createValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $validated = $validator->validated();
        $validated['created_by'] = Auth::id();

        $result = $this->chatbotService->createQuestion($validated);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            $result['message']
        );
    }

    /**
     * Update an existing chatbot question.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->updateValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $validated = $validator->validated();
        $validated['updated_by'] = Auth::id();

        $result = $this->chatbotService->updateQuestion($id, $validated);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            $result['message']
        );
    }

    /**
     * Update chatbot question flow line.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLineFlow(int $id, Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->updateLineFlowValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $validated = $validator->validated();

        $isUpdated = $this->chatbotService->updateLineFlow($id, [
            'next_question_id' => $validated['next_question_id'] ?? null,
            'updated_by' => Auth::id(),
        ]);

        if (! $isUpdated) {
            return $this->errorResponse(
                __('chatbot.messages.update_error'),
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            __('chatbot.messages.update_success')
        );
    }

    /**
     * Update chatbot question position by identifier.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePosition(int $id, Request $request): JsonResponse
    {
        $validator = $this->chatbotValidation->updatePositionValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $validated = $validator->validated();
        $validated['updated_by'] = Auth::id();

        $result = $this->chatbotService->updatePosition($id, $validated);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            $result['message']
        );
    }

    /**
     * Delete a chatbot question by identifier.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->chatbotValidation->detailValidation($id);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('chatbot.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->chatbotService->deleteQuestion($id, Auth::id());

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            null,
            $result['message']
        );
    }
}
