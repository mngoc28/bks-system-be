<?php

namespace Database\Seeders;

use App\Models\ChatbotAnswer;
use App\Models\ChatbotQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('chatbot_answers')->truncate();
        DB::table('chatbot_questions')->truncate();

        $questions = [];
        $answers = [];
        $timestamp = now();

        for ($index = 1; $index <= 20; $index++) {
            $questions[] = [
                'content' => "Sample question {$index}",
                'type' => 0,
                'position_x' => $index * 10,
                'position_y' => $index * 15,
                'is_start_node' => $index === 1 ? 1 : 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        ChatbotQuestion::query()->insert($questions);

        for ($questionId = 1; $questionId <= 20; $questionId++) {
            $answers[] = [
                'question_id' => $questionId,
                'content' => "Answer A for question {$questionId}",
                'next_question_id' => $questionId < 20 ? $questionId + 1 : null,
                'is_final' => $questionId === 20 ? 1 : 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $answers[] = [
                'question_id' => $questionId,
                'content' => "Answer B for question {$questionId}",
                'next_question_id' => null,
                'is_final' => 0,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        ChatbotAnswer::query()->insert($answers);
    }
}
