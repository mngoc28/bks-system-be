<?php

namespace App\Repositories\ChatbotQuestionRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface ChatbotQuestionRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieve chatbot questions with aggregated information.
     *
     * @param Request $request
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getList(Request $request);

    /**
     * Retrieve chatbot question flows using shared listing query.
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function listQuestionFlows(Request $request): array;

    /**
     * Find a chatbot question along with its answers.
     *
     * @param int $id
     * @return array|null
     */
    public function findWithAnswers(int $id): ?array;

    /**
     * Retrieve start chatbot question with answers.
     *
     * @param Request $request
     * @return array|null
     */
    public function getStartQuestion(Request $request): ?array;

    /**
     * Retrieve chatbot question detail with answers.
     *
     * @param int $id
     * @return array|null
     */
    public function getDetailChatbotQuestion(int $id): ?array;
}
