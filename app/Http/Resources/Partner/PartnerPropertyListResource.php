<?php

declare(strict_types=1);

namespace App\Http\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int|null $id
 * @property string|null $name
 * @property string|null $address_detail
 * @property int|null $property_type_id
 * @property string|null $rent_category
 * @property string|null $province_id
 * @property string|null $ward_id
 * @property string|null $description
 * @property float|null $area
 * @property int|null $year_built
 * @property string|null $province_name
 * @property string|null $ward_name
 * @property int|null $rooms_count
 * @property int|null $vacant_rooms_count
 * @property string|null $cover_image_url
 * @property int|null $reviews_count
 * @property float|null $reviews_avg_rating
 * @mixin \App\Models\Property
 */
final class PartnerPropertyListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $roomsCount = (int) ($this->rooms_count ?? 0);
        $vacantRoomsCount = (int) ($this->vacant_rooms_count ?? 0);
        $vacancyRate = $roomsCount > 0
            ? (int) round(($vacantRoomsCount / $roomsCount) * 100)
            : null;

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
            'rooms_count'        => $roomsCount,
            'vacant_rooms_count' => $vacantRoomsCount,
            'vacancy_rate'       => $vacancyRate,
            'reviews_count'      => (int) ($this->reviews_count ?? 0),
            'reviews_avg_rating' => isset($this->reviews_avg_rating) && $this->reviews_avg_rating !== null
                ? round((float) $this->reviews_avg_rating, 1)
                : null,
        ];

        if (isset($this->cover_image_url) && $this->cover_image_url !== null) {
            $data['cover_image_url'] = $this->cover_image_url;
        }

        if ($this->relationLoaded('rooms')) {
            $data['rooms'] = PartnerRoomPreviewResource::collection($this->rooms)->resolve();
        }

        return $data;
    }
}
