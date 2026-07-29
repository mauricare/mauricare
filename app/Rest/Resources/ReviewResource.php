<?php

namespace App\Rest\Resources;

use App\Models\Review;
use Lomkit\Rest\Http\Requests\RestRequest;

class ReviewResource extends Resource
{
    public static $model = Review::class;

    public function fields(RestRequest $request): array
    {
        return [
            'id',
            'care_booking_id',
            'reviewer_id',
            'reviewee_id',
            'rating',
            'comment',
            'created_at',
            'updated_at',
        ];
    }

    public function relations(RestRequest $request): array
    {
        return [];
    }

    public function limits(RestRequest $request): array
    {
        return [10, 25, 50];
    }
}
