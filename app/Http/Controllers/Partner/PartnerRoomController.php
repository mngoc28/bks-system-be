<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomsValidation;
use App\Models\PricePackage;
use App\Services\RoomsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerRoomController extends Controller
{
    /**
     * Service layer that handles business logic for rooms.
     * Validation layer that handles request data validation for rooms.
     */
    protected RoomsService $roomsService;
    protected RoomsValidation $roomsValidation;
    protected \App\Services\RoomImageService $roomImageService;

    /**
     * Constructor method.
     *
     * @param RoomsService $roomsService       Handles business logic for rooms
     * @param RoomsValidation $roomsValidation Validates input data for rooms
     * @param \App\Services\RoomImageService $roomImageService Handles room images
     */
    public function __construct(
        RoomsService $roomsService,
        RoomsValidation $roomsValidation,
        \App\Services\RoomImageService $roomImageService
    ) {
        $this->roomsService    = $roomsService;
        $this->roomsValidation = $roomsValidation;
        $this->roomImageService = $roomImageService;
    }

    /**
     * Handle the incoming request to search for rooms or get all rooms for partner.
     *
     * @param Request $request The incoming HTTP request containing search parameters
     * @return JsonResponse A JSON response containing the search results or errors
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->searchRoomValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->roomsService->handleGetAllRoomsForPartner($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Handle the incoming request to get a room by its ID for partner.
     *
     * @param int $id The ID of the room to retrieve
     * @return JsonResponse A JSON response containing the room details or errors
     */
    public function show($id): JsonResponse
    {
        $result = $this->roomsService->handleGetRoomDetailForPartner((int)$id);
        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::NOT_FOUND
            );
        }
        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Create a new room for the partner portal (simplified form).
     * Maps the simple partner form fields to what the service layer expects.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Normalize: accept propertyId (camelCase) or property_id / legacy propertyId / property_id
        $propertyId = $request->input('property_id')
            ?? $request->input('propertyId');
        $title      = $request->input('title') ?? $request->input('name');

        // Simple validation for partner portal form
        $validator = \Illuminate\Support\Facades\Validator::make(
            array_merge($request->all(), [
                'property_id' => $propertyId,
                'title'       => $title,
            ]),
            [
                'property_id' => ['required', 'integer', 'exists:properties,id'],
                'title'       => ['required', 'string', 'max:255'],
                'area'        => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'property_id.required' => 'Vui lòng chọn bất động sản.',
                'property_id.exists'   => 'Bất động sản không tồn tại.',
                'title.required'       => 'Vui lòng nhập tên phòng.',
            ]
        );

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        // Build request-compatible data merging defaults for required fields
        $request->merge([
            'property_id' => (int) $propertyId,
            'title'       => $title,
            'room_number' => $title, // use title as room number if not provided
            'floor_number'=> $request->input('floor_number', 1),
            'people'      => $request->input('people', 1),
            'room_type'   => $request->input('room_type', 1),
            'status'      => $request->input('status', true),
            'area'        => $request->input('area', 0),
            'amenities'   => $request->input('amenities', []),
            'services'    => $request->input('services', []),
            'prices'      => $this->normalizePrices($request->input('prices', [])),
            'utility_fees'=> $request->input('utility_fees', []),
        ]);

        $result = $this->roomsService->createRoom($request);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->createdResponse($result['data'], $result['message']);
    }

    /**
     * Update a room for the partner portal (simplified form).
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id'   => ['required', 'integer', 'exists:rooms,id'],
                'area' => ['nullable', 'numeric', 'min:0'],
            ],
            [
                'id.exists' => 'Phòng không tồn tại.',
            ]
        );

        if ($validator->fails()) {
            return $this->validateError($validator->errors(), null, HttpStatus::VALIDATION_ERROR);
        }

        // Normalize title field
        if (!$request->has('title') && $request->has('name')) {
            $request->merge(['title' => $request->input('name')]);
        }

        // Ensure amenities/services/prices are arrays (can be empty for update)
        $request->merge([
            'amenities' => $request->input('amenities', []),
            'services'  => $request->input('services', []),
            'prices'    => $this->normalizePrices($request->input('prices', [])),
            'utility_fees' => $request->input('utility_fees', []),
        ]);

        $result = $this->roomsService->updateRoom($request, (int) $id);

        if (! $result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get price packages
     *
     * @return JsonResponse
     */
    public function getPricePackages(): JsonResponse
    {
        $result = $this->roomsService->handleGetPricePackages();
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Get occupancy statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function occupancy(Request $request): JsonResponse
    {
        $result = $this->roomsService->handleGetRoomsOccupancy($request);

        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }

        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Bulk store rooms
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkStore(Request $request): JsonResponse
    {
        $request->merge([
            'prices' => $this->normalizePrices($request->input('prices', [])),
            'utility_fees' => $request->input('utility_fees', []),
        ]);

        $result = $this->roomsService->handleBulkCreateRooms($request);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->createdResponse($result['data'], $result['message']);
    }

    /**
     * Bulk update status
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'ids'    => 'required|array',
            'status' => 'required',
        ]);

        $result = $this->roomsService->handleBulkUpdateStatus($request->ids, $request->status);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Bulk delete rooms
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        $result = $this->roomsService->handleBulkDeleteRooms($request->ids);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse(null, $result['message']);
    }

    /**
     * Get room images
     */
    public function getImages($id): JsonResponse
    {
        $result = $this->roomImageService->getByRoomId((int)$id);
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Add room images (Cloudinary)
     */
    public function storeImages(Request $request, $id): JsonResponse
    {
        $request->validate([
            'image_url' => 'required|string',
            'id_image_cloudinary' => 'required|string',
            'image_type' => 'nullable|integer',
        ]);

        $data = $request->all();
        $data['room_id'] = (int)$id;
        $data['image_type'] = $data['image_type'] ?? 1;

        $result = $this->roomImageService->store($data);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse($result['data'], $result['message']);
    }

    /**
     * Delete room image
     */
    public function deleteImage($id, $imageId): JsonResponse
    {
        $result = $this->roomImageService->destroy((int)$imageId);
        if (!$result['success']) {
            return $this->errorResponse($result['message'], null, HttpStatus::BAD_REQUEST);
        }
        return $this->successResponse(null, $result['message']);
    }

    /**
     * Normalize frontend prices to RoomPriceService schema.
     * Accepts both new format (price_package_id, unit, unit_price)
     * and legacy partner form format (packageName, price, duration).
     *
     * @param mixed $prices
     * @return array<int, array<string, mixed>>
     */
    private function normalizePrices(mixed $prices): array
    {
        if (!is_array($prices)) {
            return [];
        }

        $defaultPackageId = (int) PricePackage::query()
            ->where('status', true)
            ->value('id');

        $normalized = [];

        foreach ($prices as $item) {
            if (!is_array($item)) {
                continue;
            }

            $packageId = isset($item['price_package_id'])
                ? (int) $item['price_package_id']
                : 0;

            if ($packageId <= 0 && !empty($item['packageName'])) {
                $matched = PricePackage::query()
                    ->where('status', true)
                    ->where('name', $item['packageName'])
                    ->value('id');

                $packageId = (int) ($matched ?? 0);
            }

            if ($packageId <= 0) {
                $packageId = $defaultPackageId;
            }

            if ($packageId <= 0) {
                continue;
            }

            $unit = (string) ($item['unit'] ?? 'month');
            if (!in_array($unit, ['day', 'month', 'year'], true)) {
                $unit = 'month';
            }

            $unitPrice = $item['unit_price'] ?? $item['price'] ?? 0;

            $normalized[] = [
                'price_package_id' => $packageId,
                'unit' => $unit,
                'unit_price' => (float) $unitPrice,
                'deposit_amount' => (float) ($item['deposit_amount'] ?? 0),
                'minimum_stay' => (int) ($item['minimum_stay'] ?? 1),
            ];
        }

        return $normalized;
    }
}
