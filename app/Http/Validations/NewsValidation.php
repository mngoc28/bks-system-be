<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class NewsValidation
{
    /**
     * Validate index request for news
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function indexValidation(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1',
            'published_at_start' => 'nullable|date|before_or_equal:published_at_end',
            'published_at_end' => 'nullable|date|after_or_equal:published_at_start',
            'status' => 'nullable|integer',
            'user_name' => 'nullable|string',
            'title' => 'nullable|string',
            'content' => 'nullable|string',
            'sort_field' => 'nullable|string',
            'sort_direction' => 'nullable|string',
        ], [
            'page.integer' => __('news.validation.page.integer'),
            'page.min' => __('news.validation.page.min'),
            'per_page.integer' => __('news.validation.per_page.integer'),
            'per_page.min' => __('news.validation.per_page.min'),
            'published_at_start.date' => __('news.validation.published_at_start.date'),
            'published_at_end.date' => __('news.validation.published_at_end.date'),
            'status.integer' => __('news.validation.status.integer'),
            'user_name.string' => __('news.validation.user_name.string'),
            'title.string' => __('news.validation.title.string'),
            'content.string' => __('news.validation.content.string'),
            'sort_field.string' => __('news.validation.sort_field.string'),
            'sort_direction.string' => __('news.validation.sort_direction.string'),
            'id.required' => __('news.validation.id.required'),
            'id.exists' => __('news.validation.id.exists'),
            'id.integer' => __('news.validation.id.integer'),
            'published_at_start.before_or_equal' => __('news.validation.check_time.error'),
            'published_at_end.after_or_equal' => __('news.validation.check_time.error'),
        ]);
    }

    /**
     *  Validate show for news
     *  @param int $id
     *  @return \Illuminate\Validation\Validator
     */
    public function showValidation(int $id): \Illuminate\Validation\Validator
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|exists:news,id|integer'
        ], [
            'id.required' => __('news.validation.id.required'),
            'id.exists' => __('news.validation.id.exists'),
            'id.integer' => __('news.validation.id.integer'),
        ]);
    }

    /**
     *  validate store for news
     *  @param Request $request
     *  @return \Illuminate\Validation\Validator
     */
    public function storeValidation(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'title' => 'required|string',
                'summary' => 'nullable|string',
                'content' => 'required|string',
                'status' => 'nullable|integer',
                'published_at' => 'nullable|date',
                'image_url' => 'nullable|string',
                'id_image_cloudinary' => 'nullable|string',
            ],
            [
                'title.string' => __('news.validation.title.string'),
                'title.required' => __('news.validation.title.required'),
                'summary.string' => __('news.validation.summary.string'),
                'content.string' => __('news.validation.content.string'),
                'content.required' => __('news.validation.content.required'),
                'status.integer' => __('news.validation.status.integer'),
                'published_at.date' => __('news.validation.published_at.date'),
            ]
        );
    }

    /**
     *  validate update for news
     *  @param Array $data
     *  @return \Illuminate\Validation\Validator
     */
    public function updateValidation(array $data): \Illuminate\Validation\Validator
    {
        return Validator::make($data, [
            'id' => 'required|exists:news,id|integer',
            'user_id' => 'required|exists:users,id|integer',
            'title' => 'required|string',
            'summary' => 'nullable|string',
            'content' => 'required|string',
            'status' => 'nullable|integer',
            'published_at' => 'nullable|date',
            'image_url' => 'nullable|string',
            'id_image_cloudinary' => 'nullable|string',
        ], [
            'id.required' => __('news.validation.id.required'),
            'id.exists' => __('news.validation.id.exists'),
            'id.integer' => __('news.validation.id.integer'),
            'user_id.required' => __('news.validation.user_id.required'),
            'user_id.exists' => __('news.validation.user_id.exists'),
            'user_id.integer' => __('news.validation.user_id.integer'),
            'title.string' => __('news.validation.title.string'),
            'title.required' => __('news.validation.title.required'),
            'summary.string' => __('news.validation.summary.string'),
            'content.required' => __('news.validation.content.required'),
            'content.string' => __('news.validation.content.string'),
            'status.integer' => __('news.validation.status.integer'),
            'published_at.date' => __('news.validation.published_at.date'),
            'image_url.string' => __('news.validation.image_url.string'),
            'id_image_cloudinary.string' => __('news.validation.id_image_cloudinary.string'),
            'image_url.url' => __('news.validation.image_url.url'),
            'id_image_cloudinary.url' => __('news.validation.id_image_cloudinary.url'),
        ]);
    }

    /**
     *  Valudate destroy for news
     *  @param int $id
     *  @retuen \Illuminate\Validation\Validator
     */
    public function destroyValidation(int $id): \Illuminate\Validation\Validator
    {
        return Validator::make(['id' => $id], [
            'id' => 'required|exists:news,id|integer'
        ], [
            'id.required' => __('news.validation.id.required'),
            'id.exists' => __('news.validation.id.exists'),
            'id.integer' => __('news.validation.id.integer'),
        ]);
    }
}
