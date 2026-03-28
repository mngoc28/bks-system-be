<?php

namespace App\Services;

use App\Repositories\ChatbotAnswerRepository\ChatbotAnswerRepositoryInterface;
use App\Repositories\ChatbotQuestionRepository\ChatbotQuestionRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ChatbotService
{
    public function __construct(
        private readonly ChatbotQuestionRepositoryInterface $questionRepository,
        private readonly ChatbotAnswerRepositoryInterface $answerRepository
    ) {
    }

    /**
     * Get chatbot question list with aggregated answer count.
     *
     * @param mixed $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getChatbotQuestionsList($request): array
    {
        try {
            $questions = $this->questionRepository->getList($request);

            return [
                'success' => true,
                'data' => $questions,
                'message' => __('chatbot.messages.fetch_success'),
            ];
        } catch (\Throwable $throwable) {
            Log::error('Failed to fetch chatbot flow', [
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.fetch_error'),
            ];
        }
    }

    /**
     * Get chatbot question flows using shared listing logic.
     *
     * @param mixed $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function listQuestionFlows($request): array
    {
        try {
            $questions = $this->questionRepository->listQuestionFlows($request);

            return [
                'success' => true,
                'data' => $questions,
                'message' => __('chatbot.messages.fetch_success'),
            ];
        } catch (\Throwable $throwable) {
            Log::error('Failed to fetch chatbot question flows', [
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.fetch_error'),
            ];
        }
    }

    /**
     * Get start question for public chatbot flow.
     *
     * @param mixed $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getStartQuestion($request): array
    {
        try {
            $question = $this->questionRepository->getStartQuestion($request);

            if (! $question) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $question,
                'message' => __('chatbot.messages.fetch_success'),
            ];
        } catch (\Throwable $throwable) {
            Log::error('Failed to fetch start chatbot question', [
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.fetch_error'),
            ];
        }
    }

    /**
     * Get next chatbot question for provided answer.
     *
     * @param int $id
     * @param mixed $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getNextQuestion(int $id, $request): array
    {
        try {
            $answer = $this->answerRepository->find($id);

            if (! $answer) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            if (! $answer->next_question_id) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            $question = $this->questionRepository->getDetailChatbotQuestion($answer->next_question_id);

            if (! $question) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            if ($request->filled('type') && (int) $question['type'] !== (int) $request->input('type')) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $question,
                'message' => __('chatbot.messages.fetch_success'),
            ];
        } catch (\Throwable $throwable) {
            Log::error('Failed to fetch next chatbot question', [
                'answer_id' => $id,
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.fetch_error'),
            ];
        }
    }

    /**
     * Get chatbot question detail with answers.
     *
     * @param int $id
     * @return array{success: bool, data: mixed, message: string}
     */
    public function getChatbotQuestionDetail(int $id): array
    {
        try {
            $question = $this->questionRepository->findWithAnswers($id);

            if (! $question) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $question,
                'message' => __('chatbot.messages.fetch_success'),
            ];
        } catch (\Throwable $throwable) {
            Log::error('Failed to fetch chatbot detail', [
                'question_id' => $id,
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.fetch_error'),
            ];
        }
    }

    /**
     * Create chatbot question and answers.
     *
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function createQuestion(array $data): array
    {
        DB::beginTransaction();

        try {
            $question = $this->questionRepository->create([
                'content' => $data['content'],
                'type' => $data['type'],
                'position_x' => $data['position_x'] ?? 0,
                'position_y' => $data['position_y'] ?? 0,
                'is_start_node' => $data['is_start_node'],
                'created_by' => $data['created_by'] ?? null,
            ]);

            if (! empty($data['answers'])) {
                $answersPayload = [];

                foreach ($data['answers'] as $answer) {
                    $answersPayload[] = [
                        'question_id' => $question->id,
                        'content' => $answer['content'],
                        'next_question_id' => $answer['next_question_id'] ?? null,
                        'is_final' => $answer['is_final'] ?? 0,
                        'created_by' => $data['created_by'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                $this->answerRepository->insert($answersPayload);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('chatbot.messages.create_success'),
            ];
        } catch (\Throwable $throwable) {
            DB::rollBack();

            Log::error('Failed to create chatbot question', [
                'payload' => $data,
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.create_error'),
            ];
        }
    }

    /**
     * Update chatbot question and answers.
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function updateQuestion(int $id, array $data): array
    {
        DB::beginTransaction();

        try {
            $question = $this->questionRepository->find($id);

            if (! $question) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            $question->update(array_filter([
                'content' => $data['content'] ?? $question->content,
                'type' => $data['type'] ?? $question->type,
                'position_x' => $data['position_x'] ?? $question->position_x,
                'position_y' => $data['position_y'] ?? $question->position_y,
                'is_start_node' => $data['is_start_node'] ?? $question->is_start_node,
                'updated_by' => $data['updated_by'] ?? null,
            ], static fn ($value) => $value !== null));

            if (! empty($data['answers'])) {
                $this->processAnswers($question->id, $data['answers'], $data['updated_by'] ?? null);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('chatbot.messages.update_success'),
            ];
        } catch (\Throwable $throwable) {
            DB::rollBack();

            Log::error('Failed to update chatbot question', [
                'question_id' => $id,
                'payload' => $data,
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.update_error'),
            ];
        }
    }

    /**
     * Process answers updates based on provided actions.
     *
     * @param int $questionId
     * @param array $answers
     * @param int|null $actorId
     * @return void
     */
    protected function processAnswers(int $questionId, array $answers, ?int $actorId = null): void
    {
        $timestamp = now();

        foreach ($answers as $answer) {
            $action = $answer['_action'] ?? 'update';

            if ($action === 'delete' && ! empty($answer['id'])) {
                $this->answerRepository->delete($answer['id']);
                continue;
            }

            $payload = [
                'question_id' => $questionId,
                'content' => $answer['content'],
                'next_question_id' => $answer['next_question_id'] ?? null,
                'is_final' => $answer['is_final'] ?? 0,
                'updated_by' => $actorId,
                'updated_at' => $timestamp,
            ];

            if ($action === 'create') {
                $payload['created_by'] = $actorId;
                $payload['created_at'] = $timestamp;
                $this->answerRepository->insert([$payload]);
            } elseif ($action === 'update' && ! empty($answer['id'])) {
                $this->answerRepository->update($answer['id'], $payload);
            }
        }
    }

    /**
     * Delete chatbot question and its answers.
     *
     * @param int $id
     * @param int|null $actorId
     * @return array{success: bool, data: mixed, message: string}
     */
    public function deleteQuestion(int $id, ?int $actorId = null): array
    {
        DB::beginTransaction();

        try {
            $question = $this->questionRepository->find($id);

            if (! $question) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            $this->answerRepository->deleteBy(['question_id' => $id]);
            $this->questionRepository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('chatbot.messages.delete_success'),
            ];
        } catch (\Throwable $throwable) {
            DB::rollBack();

            Log::error('Failed to delete chatbot question', [
                'question_id' => $id,
                'actor_id' => $actorId,
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.delete_error'),
            ];
        }
    }

    /**
     * Update chatbot question flow line.
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function updateLineFlow(int $id, array $data): bool
    {
        DB::beginTransaction();

        try {
            $answer = $this->answerRepository->find($id);

            if (! $answer) {
                DB::rollBack();

                return false;
            }

            $payload = [
                'next_question_id' => $data['next_question_id'] ?? null,
                'updated_at' => now(),
                'updated_by' => $data['updated_by'] ?? null,
            ];

            $this->answerRepository->update($id, $payload);

            DB::commit();

            return true;
        } catch (\Throwable $throwable) {
            DB::rollBack();

            Log::error('Failed to update chatbot line flow', [
                'answer_id' => $id,
                'payload' => $data,
                'exception' => $throwable,
            ]);

            return false;
        }
    }

    /**
     * Update chatbot question position details.
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function updatePosition(int $id, array $data): array
    {
        DB::beginTransaction();

        try {
            $question = $this->questionRepository->find($id);

            if (! $question) {
                DB::rollBack();

                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('chatbot.messages.not_found'),
                ];
            }

            $this->questionRepository->update($id, [
                'position_x' => $data['position_x'],
                'position_y' => $data['position_y'],
                'updated_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('chatbot.messages.update_success'),
            ];
        } catch (\Throwable $throwable) {
            DB::rollBack();

            Log::error('Failed to update chatbot question position', [
                'question_id' => $id,
                'payload' => $data,
                'exception' => $throwable,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('chatbot.messages.update_error'),
            ];
        }
    }
}
