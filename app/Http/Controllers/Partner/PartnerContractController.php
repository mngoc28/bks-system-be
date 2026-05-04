<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Services\ContractService;
use App\Enums\HttpStatus;
use App\Traits\ApiResponser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PartnerContractController extends Controller
{
    use ApiResponser;

    /**
     * @var ContractService
     */
    protected $contractService;

    /**
     * PartnerContractController constructor.
     *
     * @param ContractService $contractService
     */
    public function __construct(ContractService $contractService)
    {
        $this->contractService = $contractService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result = $this->contractService->handleGetPartnerContracts();

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
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|integer',
            'title'      => 'required|string|max:255',
            'content'    => 'required|string',
        ]);

        $result = $this->contractService->handleCreateContract($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->createdResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->contractService->handleGetPartnerContractDetail($id);

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
}
