<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class ChatbotValidation
{
    /**
     * Validate request for chatbot flow listing.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function listValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'pagination' => ['nullable', 'integer', 'min:1'],
            'sort_by' => ['nullable', 'string'],
            'direction' => ['nullable', 'string', 'in:asc,desc'],
        ], [
            'pagination.integer' => __('chatbot.pagination_integer'),
            'pagination.min' => __('chatbot.pagination_min'),
            'sort_by.string' => __('chatbot.sort_by_string'),
            'direction.string' => __('chatbot.direction_string'),
            'direction.in' => __('chatbot.direction_invalid'),
        ]);
    }

    /**
     * Validate question detail request.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function detailValidation(int $id)
    {
        return Validator::make(['id' => $id], [
            'id' => ['required', 'integer', 'exists:chatbot_questions,id'],
        ], [
            'id.required' => __('chatbot.id_required'),
            'id.integer' => __('chatbot.id_integer'),
            'id.exists' => __('chatbot.id_exists'),
        ]);
    }

    /**
     * Validate request to create chatbot question.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function createValidation(Request $request)
    {
        return Validator::make($request->all(), [
            'content' => ['required', 'string'],
            'type' => ['required', 'integer'],
            'is_start_node' => ['required', 'integer', 'in:0,1'],
            'answers' => ['nullable', 'array'],
            'answers.*.content' => ['required_with:answers', 'string'],
            'answers.*.next_question_id' => ['nullable', 'integer', 'exists:chatbot_questions,id'],
            'answers.*.is_final' => ['nullable', 'integer', 'in:0,1'],
        ], [
            'content.required' => __('chatbot.content_required'),
            'content.string' => __('chatbot.content_string'),
            'type.required' => __('chatbot.type_required'),
            'type.integer' => __('chatbot.type_integer'),
            'is_start_node.required' => __('chatbot.is_start_node_required'),
            'is_start_node.integer' => __('chatbot.is_start_node_integer'),
            'is_start_node.in' => __('chatbot.is_start_node_in'),
            'answers.array' => __('chatbot.answers_array'),
            'answers.*.content.required_with' => __('chatbot.answer_content_required'),
            'answers.*.content.string' => __('chatbot.answer_content_string'),
            'answers.*.next_question_id.integer' => __('chatbot.answer_next_integer'),
            'answers.*.next_question_id.exists' => __('chatbot.answer_next_exists'),
            'answers.*.is_final.integer' => __('chatbot.answer_is_final_integer'),
            'answers.*.is_final.in' => __('chatbot.answer_is_final_in'),
        ]);
    }

    /**
     * Validate request to update chatbot question.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateValidation(int $id, Request $request)
    {
        return Validator::make($request->all(), [
            'content' => ['nullable', 'string'],
            'type' => ['nullable', 'integer'],
            'is_start_node' => ['nullable', 'integer', 'in:0,1'],
            'answers' => ['nullable', 'array'],
            'answers.*.id' => [
                'nullable',
                'integer',
                'exists:chatbot_answers,id',
                'required_if:answers.*._action,update',
                'required_if:answers.*._action,delete',
            ],
            'answers.*.content' => ['required_with:answers', 'string'],
            'answers.*.next_question_id' => ['nullable', 'integer', 'exists:chatbot_questions,id'],
            'answers.*.is_final' => ['nullable', 'integer', 'in:0,1'],
            'answers.*._action' => ['nullable', 'in:create,update,delete'],
        ], [
            'content.string' => __('chatbot.content_string'),
            'type.integer' => __('chatbot.type_integer'),
            'is_start_node.integer' => __('chatbot.is_start_node_integer'),
            'is_start_node.in' => __('chatbot.is_start_node_in'),
            'answers.array' => __('chatbot.answers_array'),
            'answers.*.id.integer' => __('chatbot.answer_id_integer'),
            'answers.*.id.exists' => __('chatbot.answer_id_exists'),
            'answers.*.id.required_if' => __('chatbot.answer_id_required'),
            'answers.*.content.required_with' => __('chatbot.answer_content_required'),
            'answers.*.content.string' => __('chatbot.answer_content_string'),
            'answers.*.next_question_id.integer' => __('chatbot.answer_next_integer'),
            'answers.*.next_question_id.exists' => __('chatbot.answer_next_exists'),
            'answers.*.is_final.integer' => __('chatbot.answer_is_final_integer'),
            'answers.*.is_final.in' => __('chatbot.answer_is_final_in'),
            'answers.*._action.in' => __('chatbot.answer_action_in'),
        ]);
    }

    /**
     * List question flow validation.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function listQuestionFlowValidation(Request $request)
    {
        return Validator::make($request->all(), []);
    }

    /**
     * Validate request to update chatbot line flow.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateLineFlowValidation(int $id, Request $request)
    {
        return Validator::make($request->all(), [
            'answer_id' => ['required', 'integer', 'exists:chatbot_answers,id'],
            'next_question_id' => ['nullable', 'integer', 'exists:chatbot_questions,id'],
        ], [
            'answer_id.required' => __('chatbot.answer_id_required'),
            'answer_id.integer' => __('chatbot.answer_id_integer'),
            'answer_id.exists' => __('chatbot.answer_id_exists'),
            'next_question_id.integer' => __('chatbot.answer_next_integer'),
            'next_question_id.exists' => __('chatbot.answer_next_exists'),
        ]);
    }

    /**
     * Validate request to update chatbot question position.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updatePositionValidation(int $id, Request $request)
    {
        return Validator::make($request->all(), [
            'position_x' => ['required', 'integer'],
            'position_y' => ['required', 'integer'],
        ], [
            'position_x.required' => __('chatbot.position_x_required'),
            'position_x.integer' => __('chatbot.position_x_integer'),
            'position_y.required' => __('chatbot.position_y_required'),
            'position_y.integer' => __('chatbot.position_y_integer'),
        ]);
    }

    /**
     * Validate request to retrieve next chatbot question.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function nextQuestionValidation(int $id, Request $request)
    {
        return Validator::make(array_merge($request->all(), ['id' => $id]), [
            'id' => ['required', 'integer', 'exists:chatbot_answers,id'],
            'type' => ['nullable', 'integer'],
        ], [
            'id.required' => __('chatbot.answer_id_required'),
            'id.integer' => __('chatbot.answer_id_integer'),
            'id.exists' => __('chatbot.answer_id_exists'),
            'type.integer' => __('chatbot.type_integer'),
        ]);
    }
}
