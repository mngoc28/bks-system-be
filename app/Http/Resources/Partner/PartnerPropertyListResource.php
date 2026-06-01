<?php

declare(strict_types=1);

namespace App\Http\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

final class PartnerPropertyListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $data = [
            'id'                 => $this->id,
            'name'               => $this->name,
            'address_detail'     => $this->address_detail,
            'property_type_id'   => $this->property_type_id,
            'rent_category'      => $this->rent_category,
            'province_id'        => $this->province_id,
            'ward_id'            => $this->ward_id,
            'description'        => $this->description,
            'area'               => $this->area,
            'year_built'         => $this->year_built,
            'province_name'      => $this->province_name ?? null,
            'ward_name'          => $this->ward_name ?? null,
            'rooms_count'        => (int) ($this->rooms_count ?? 0),
            'reviews_count'      => (int) ($this->reviews_count ?? 0),
            'reviews_avg_rating' => isset($this->reviews_avg_rating) && $this->reviews_avg_rating !== null
                ? round((float) $this->reviews_avg_rating, 1)
                : null,
        ];

        if ($this->relationLoaded('rooms')) {
            $data['rooms'] = PartnerRoomPreviewResource::collection($this->rooms)->resolve();
        }

        return $data;
    }
}
