<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\HttpStatus;
use App\Enums\Status;
use App\Models\User;
use App\Models\PartnerInfo;
use App\Models\Notification;
use App\Jobs\SendPartnerApprovedMail;
use App\Jobs\SendPartnerRejectedMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

final class PartnerApprovalController extends Controller
{
    /**
     * Get a list of partner applications pending review.
     *
     * @return JsonResponse
     */
    public function pendingList(): JsonResponse
    {
        try {
            $pendingPartners = User::where('role', 'partner')
                ->whereIn('status', [Status::PENDING_APPROVAL->value, Status::REJECTED->value])
                ->with(['partnerInfo.province', 'partnerInfo.ward'])
                ->orderBy('updated_at', 'desc')
                ->get();

            return $this->successResponse($pendingPartners, 'Lấy danh sách hồ sơ chờ duyệt thành công.');
        } catch (\Exception $e) {
            Log::error('Pending list fetch failed: ' . $e->getMessage());
            return $this->errorResponse('Lỗi hệ thống: ' . $e->getMessage(), null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get details of a single partner applicant.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function detail(int $id): JsonResponse
    {
        try {
            $partner = User::where('role', 'partner')
                ->with(['partnerInfo.province', 'partnerInfo.ward'])
                ->find($id);

            if (!$partner) {
                return $this->errorResponse('Không tìm thấy đối tác.', null, HttpStatus::NOT_FOUND);
            }

            return $this->successResponse($partner, 'Lấy thông tin đối tác thành công.');
        } catch (\Exception $e) {
            Log::error('Partner detail fetch failed: ' . $e->getMessage());
            return $this->errorResponse('Lỗi hệ thống: ' . $e->getMessage(), null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Verify (Approve or Reject) a partner onboarding application.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function verify(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:approve,reject',
            'rejection_reason' => 'required_if:action,reject|string|nullable'
        ]);

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        try {
            DB::beginTransaction();

            $admin = Auth::guard('api')->user();
            $partner = User::where('role', 'partner')->find($id);

            if (!$partner) {
                return $this->errorResponse('Không tìm thấy đối tác.', null, HttpStatus::NOT_FOUND);
            }

            $partnerInfo = PartnerInfo::where('user_id', $partner->id)->first();
            if (!$partnerInfo) {
                return $this->errorResponse('Không tìm thấy hồ sơ chi tiết của đối tác.', null, HttpStatus::NOT_FOUND);
            }

            $action = $request->input('action');

            if ($action === 'approve') {
                // Update User status to ACTIVE (1)
                $partner->status = Status::ACTIVE->value;
                $partner->save();

                // Update Partner Info with approval stamps
                $partnerInfo->approved_at = now();
                $partnerInfo->approved_by = $admin->id;
                $partnerInfo->rejection_reason = null; // Clear any previous rejection reason
                $partnerInfo->save();

                Notification::create([
                    'user_id' => $partner->id,
                    'title' => 'Hồ sơ đã được phê duyệt',
                    'message' => 'Chúc mừng! Hồ sơ đối tác của bạn đã được phê duyệt. ' .
                        'Bạn có thể bắt đầu đăng tải tài sản ngay bây giờ.',
                    'type' => 'system',
                ]);

                DB::commit();

                SendPartnerApprovedMail::dispatch($partner->name, $partner->email);

                return $this->successResponse(null, 'Phê duyệt hồ sơ đối tác thành công! Tài khoản đã được kích hoạt.');
            } else {
                // Update User status to REJECTED (4)
                $partner->status = Status::REJECTED->value;
                $partner->save();

                // Update rejection reason
                $partnerInfo->rejection_reason = $request->input('rejection_reason');
                $partnerInfo->save();

                Notification::create([
                    'user_id' => $partner->id,
                    'title' => 'Hồ sơ bị từ chối',
                    'message' => 'Hồ sơ đối tác của bạn bị từ chối. Lý do: ' . $request->input('rejection_reason'),
                    'type' => 'system',
                ]);

                DB::commit();

                SendPartnerRejectedMail::dispatch($partner->name, $partner->email, $partnerInfo->rejection_reason);

                return $this->successResponse(null, 'Đã từ chối hồ sơ đối tác và gửi lý do phản hồi thành công.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Partner verification failed: ' . $e->getMessage());
            return $this->errorResponse('Lỗi hệ thống: ' . $e->getMessage(), null, HttpStatus::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Serve private documents securely.
     *
     * @param Request $request
     * @return mixed
     */
    public function viewPrivateDocument(Request $request)
    {
        $path = $request->input('path');
        if (empty($path)) {
            return response()->json(['message' => 'Đường dẫn tệp là bắt buộc.'], 400);
        }

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['message' => 'Không được phép truy cập.'], 401);
        }

        // Security Check: Only Admin can view all, Partners can only view their own
        if ($user->role !== 'admin') {
            $parts = explode('/', $path);
            $isInvalidPath = count($parts) < 3 ||
                $parts[0] !== 'private' ||
                $parts[1] !== 'partners' ||
                (int) $parts[2] !== (int) $user->id;

            if ($isInvalidPath) {
                return response()->json([
                    'message' => 'Từ chối truy cập. Bạn chỉ có quyền xem tài liệu của chính mình.'
                ], 403);
            }
        }

        if (!Storage::disk('local')->exists($path)) {
            return response()->json(['message' => 'Không tìm thấy tệp tài liệu.'], 404);
        }

        $fileContent = Storage::disk('local')->get($path);
        $mimeType = Storage::disk('local')->mimeType($path);

        return response($fileContent)->header('Content-Type', $mimeType);
    }
}
