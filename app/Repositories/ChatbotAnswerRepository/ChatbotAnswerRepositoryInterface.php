<?php

namespace App\Repositories\ChatbotAnswerRepository;

use App\Repositories\RepositoryInterface;

interface ChatbotAnswerRepositoryInterface extends RepositoryInterface
{
    public function getAllGroupedByQuestion(): array;
}
