<?php

namespace App\Repositories\ChatbotQuestionRepository;

use App\Models\ChatbotQuestion;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ChatbotQuestionRepository extends BaseRepository implements ChatbotQuestionRepositoryInterface
{
    /**
     * Get the corresponding model class.
     *
     * @return string
     */
    public function getModel(): string
    {
        return ChatbotQuestion::class;
    }

    /**
     * Retrieve chatbot questions with aggregated answer information.
     *
     * @param Request $request
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getList(Request $request)
    {
        $query = $this->model
            ->newQuery()
            ->select([
                'chatbot_questions.id',
                'chatbot_questions.content',
                'chatbot_questions.type',
                'chatbot_questions.position_x',
                'chatbot_questions.position_y',
                'chatbot_questions.is_start_node',
                'chatbot_questions.created_at',
                'chatbot_questions.updated_at',
            ])
            ->selectRaw('COUNT(chatbot_answers.id) AS total_answers')
            ->leftJoin('chatbot_answers', 'chatbot_answers.question_id', '=', 'chatbot_questions.id')
            ->groupBy([
                'chatbot_questions.id',
                'chatbot_questions.content',
                'chatbot_questions.type',
                'chatbot_questions.position_x',
                'chatbot_questions.position_y',
                'chatbot_questions.is_start_node',
                'chatbot_questions.created_at',
                'chatbot_questions.updated_at',
            ]);

        if ($request->filled('content')) {
            $query->where(
                'chatbot_questions.content',
                'like',
                '%' . $request->input('content') . '%'
            );
        }

        $sortBy = $request->input('sort_by');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'total_answers') {
            $query->orderBy('total_answers', $direction);
        } elseif (
            $sortBy
            && in_array(
                $sortBy,
                ['id', 'content', 'type', 'position_x', 'position_y', 'is_start_node', 'created_at', 'updated_at'],
                true
            )
        ) {
            $query->orderBy('chatbot_questions.' . $sortBy, $direction);
        } else {
            $query->orderBy('chatbot_questions.id', 'desc');
        }

        if ($request->filled('pagination')) {
            $perPage = $request->input('pagination');

            if (! is_numeric($perPage) || (int) $perPage < 1) {
                $perPage = config('const.DEFAULT_PER_PAGE');
            }

            return $query->paginate((int) $perPage)->appends($request->query());
        }

        return $query->get();
    }

    /**
     * Retrieve chatbot question flows using primary listing query.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function listQuestionFlows(Request $request): array
    {
        return $this->model
            ->newQuery()
            ->with(['answers' => function ($query) {
                $query->select('id', 'question_id', 'content', 'next_question_id');
            }])
            ->select([
                'chatbot_questions.id',
                'chatbot_questions.content',
                'chatbot_questions.type',
                'chatbot_questions.position_x',
                'chatbot_questions.position_y',
                'chatbot_questions.is_start_node',
            ])
            ->orderBy('chatbot_questions.id', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Find a chatbot question with its answers.
     *
     * @param int $id
     * @return array|null
     */
    public function findWithAnswers(int $id): array|null
    {
        $question = $this->model
            ->newQuery()
            ->with(['answers' => function ($query) {
                $query->select('id', 'question_id', 'content', 'next_question_id')
                    ->orderBy('id');
            }])
            ->select([
                'chatbot_questions.id',
                'chatbot_questions.content',
                'chatbot_questions.type',
                'chatbot_questions.position_x',
                'chatbot_questions.position_y',
                'chatbot_questions.is_start_node',
            ])
            ->find($id);

        if (! $question) {
            return null;
        }

        return $question->toArray();
    }

    /**
     * Retrieve start chatbot question with associated answers.
     *
     * @param Request $request
     * @return array|null
     */
    public function getStartQuestion(Request $request): ?array
    {
        $query = $this->model
            ->newQuery()
            ->with(['answers' => function ($relation) {
                $relation->select('id', 'question_id', 'content', 'next_question_id')
                    ->orderBy('id');
            }])
            ->select([
                'chatbot_questions.id',
                'chatbot_questions.content',
                'chatbot_questions.type',
                'chatbot_questions.is_start_node',
            ])
            ->where('chatbot_questions.is_start_node', 1)
            ->orderBy('chatbot_questions.id');

        if ($request->filled('type')) {
            $query->where('chatbot_questions.type', $request->input('type'));
        }

        $question = $query->first();

        return $question?->toArray();
    }

    /**
     * Retrieve chatbot question detail with answers.
     *
     * @param int $id
     * @return array|null
     */
    public function getDetailChatbotQuestion(int $id): ?array
    {
        $question = $this->model
            ->newQuery()
            ->with(['answers' => function ($relation) {
                $relation->select('id', 'question_id', 'content', 'next_question_id')
                    ->orderBy('id');
            }])
            ->select([
                'chatbot_questions.id',
                'chatbot_questions.content',
                'chatbot_questions.type',
                'chatbot_questions.position_x',
                'chatbot_questions.position_y',
                'chatbot_questions.is_start_node',
            ])
            ->find($id);

        return $question?->toArray();
    }
}
