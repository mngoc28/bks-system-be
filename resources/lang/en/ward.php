<?php

return [
    'messages' => [
        'get_wards_by_province_id_failed' => 'Failed to get wards by province id.',
        'get_wards_by_province_id_success' => 'Wards by province id retrieved successfully.',
        'get_wards_by_province_id_not_found' => 'Wards by province id not found.',
    ],
    'validation' => [
        'province_id' => [
            'required' => 'Province ID is required.',
            'integer' => 'Province ID must be an integer.',
            'exists' => 'Province ID does not exist.',
        ],
    ],
];
