<?php

namespace App\Repositories\ChatbotAnswerRepository;

use App\Models\ChatbotAnswer;
use App\Repositories\BaseRepository;

class ChatbotAnswerRepository extends BaseRepository implements ChatbotAnswerRepositoryInterface
{
    /**
     * Get the corresponding model class.
     *
     * @return string
     */
    public function getModel(): string
    {
        return ChatbotAnswer::class;
    }

    /**
     * Retrieve all chatbot answers grouped by question ID.
     *
     * @return array
     */
    public function getAllGroupedByQuestion(): array
    {
        return $this->model
            ->newQuery()
            ->orderBy('id')
            ->get()
            ->groupBy('question_id')
            ->map(fn ($items) => $items->toArray())
            ->toArray();
    }
}
