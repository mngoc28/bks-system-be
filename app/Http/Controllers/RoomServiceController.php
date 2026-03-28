<?php

namespace App\Http\Controllers;

use App\Http\Validations\RoomServiceValidation;
use App\Services\RoomServiceService;

class RoomServiceController extends Controller
{
    /**
     * Service layer that handles business logic for services.
     * Validation layer that handles request data validation for services.
     */
    protected RoomServiceValidation $roomServiceValidation;
    protected RoomServiceService $roomServiceService;
    /**
     * Constructor method.
     *
     * Laravel automatically injects the dependencies (RoomServiceService and RoomServiceValidation)
     * using Dependency Injection.
     *
     * @param RoomServiceService $roomServiceService       Handles business logic for room services
     * @param RoomServiceValidation $roomServiceValidation Validates input data for room services
     */
    public function __construct(RoomServiceValidation $roomServiceValidation, RoomServiceService $roomServiceService)
    {
        $this->roomServiceValidation = $roomServiceValidation;
        $this->roomServiceService = $roomServiceService;
    }
}
