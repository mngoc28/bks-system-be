<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscription;
use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Display a listing of newsletter subscriptions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = NewsletterSubscription::with('coupon')->orderBy('created_at', 'desc');

        // Filter by email search term
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }

        // Filter by subscription status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $paginated = $query->paginate($perPage);

        return $this->successResponse(
            [
                'items' => $paginated->items(),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                ]
            ],
            'Lấy danh sách đăng ký nhận tin thành công.'
        );
    }

    /**
     * Update status of a newsletter subscription.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $subscription = NewsletterSubscription::find($id);

        if (!$subscription) {
            return $this->errorResponse(
                'Không tìm thấy thông tin đăng ký.',
                null,
                HttpStatus::NOT_FOUND
            );
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:subscribed,unsubscribed',
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors());
        }

        $subscription->update([
            'status' => $request->input('status'),
        ]);

        return $this->successResponse(
            $subscription,
            'Cập nhật trạng thái đăng ký thành công.'
        );
    }

    /**
     * Remove the specified subscription.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $subscription = NewsletterSubscription::find($id);

        if (!$subscription) {
            return $this->errorResponse(
                'Không tìm thấy thông tin đăng ký.',
                null,
                HttpStatus::NOT_FOUND
            );
        }

        $subscription->delete();

        return $this->successResponse(
            null,
            'Xóa thông tin đăng ký thành công.'
        );
    }
}
