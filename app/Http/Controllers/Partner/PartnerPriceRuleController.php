<?php

declare(strict_types=1);

namespace App\Http\Controllers\Partner;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Models\PriceRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

final class PartnerPriceRuleController extends Controller
{
    /**
     * List all price rules for a property
     */
    public function index(Request $request): JsonResponse
    {
        $propertyId = $request->query('property_id');
        $rules = PriceRule::where('property_id', $propertyId)
            ->with(['room'])
            ->get();
        return $this->successResponse($rules, 'Price rules retrieved successfully');
    }

    /**
     * Store a new price rule
     */
    public function store(Request $request): JsonResponse
    {
        $partnerId = Auth::id();
        $data = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'room_id'     => 'nullable|exists:rooms,id',
            'name'        => 'required|string|max:255',
            'type'        => 'required|in:markup,discount',
            'value_type'  => 'required|in:percentage,fixed',
            'value'       => 'required|numeric|min:0',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'days_of_week'=> 'nullable|array',
        ]);

        $data['created_by'] = $partnerId;
        $data['updated_by'] = $partnerId;

        $rule = PriceRule::create($data);
        return $this->successResponse($rule, 'Price rule created successfully');
    }

    /**
     * Update a price rule
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $rule = PriceRule::find($id);
        if (!$rule) {
            return $this->errorResponse('Price rule not found', null, HttpStatus::NOT_FOUND);
        }

        $partnerId = Auth::id();
        $data = $request->validate([
            'name'        => 'sometimes|string|max:255',
            'type'        => 'sometimes|in:markup,discount',
            'value_type'  => 'sometimes|in:percentage,fixed',
            'value'       => 'sometimes|numeric|min:0',
            'start_date'  => 'sometimes|date',
            'end_date'    => 'sometimes|date|after_or_equal:start_date',
            'days_of_week'=> 'nullable|array',
            'is_active'   => 'sometimes|boolean',
        ]);

        $data['updated_by'] = $partnerId;

        $rule->update($data);
        return $this->successResponse($rule, 'Price rule updated successfully');
    }

    /**
     * Delete a price rule
     */
    public function destroy(int $id): JsonResponse
    {
        $rule = PriceRule::find($id);
        if (!$rule) {
            return $this->errorResponse('Price rule not found', null, HttpStatus::NOT_FOUND);
        }

        $rule->delete();
        return $this->successResponse(null, 'Price rule deleted successfully');
    }
}
