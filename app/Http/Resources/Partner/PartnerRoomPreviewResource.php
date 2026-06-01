<?php

declare(strict_types=1);

namespace App\Http\Resources\Partner;

use Illuminate\Http\Resources\Json\JsonResource;

final class PartnerRoomPreviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'property_id'        => $this->property_id,
            'room_number'        => $this->room_number,
            'title'              => $this->title,
            'room_type'          => $this->room_type,
            'status'             => $this->status,
            'area'               => $this->area,
            'people'             => $this->people,
            'reviews_count'      => (int) ($this->reviews_count ?? 0),
            'reviews_avg_rating' => $this->reviews_avg_rating !== null
                ? round((float) $this->reviews_avg_rating, 1)
                : null,
            'amenities'          => $this->whenLoaded(
                'amenities',
                fn () => $this->amenities->pluck('name')->values()->all()
            ),
            'services'           => $this->whenLoaded(
                'services',
                fn () => $this->services->pluck('name')->values()->all()
            ),
            'prices'             => $this->whenLoaded(
                'prices',
                fn () => $this->prices->map(static fn ($price): array => [
                    'id'               => $price->id,
                    'price_package_id' => $price->price_package_id,
                    'unit'             => $price->unit,
                    'price'            => $price->price,
                    'deposit_amount'   => $price->deposit_amount,
                    'minimum_stay'     => $price->minimum_stay,
                ])->values()->all()
            ),
        ];
    }
}
