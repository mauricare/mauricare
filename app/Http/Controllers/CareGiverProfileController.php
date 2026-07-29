<?php

namespace App\Http\Controllers;

use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CareGiverProfileController extends Controller
{
    public function show(Request $request, User $careGiver): JsonResponse
    {
        $canView = CareBooking::where('user_id', $request->user()->id)
            ->where('care_giver_id', $careGiver->id)
            ->exists();

        abort_unless($canView, 403, 'You can only view care givers linked to one of your bookings.');

        $careGiver->load(['profile', 'careGiverProfile', 'media']);

        $reviews = $careGiver->reviewsReceived()
            ->with('reviewer.media')
            ->latest('id')
            ->get(['id', 'reviewer_id', 'rating', 'comment', 'created_at']);

        return response()->json([
            'data' => [
                'id' => $careGiver->id,
                'name' => $careGiver->name,
                'avatar_url' => $careGiver->avatar_url,
                'type' => $careGiver->careGiverProfile?->type,
                'age' => $careGiver->profile?->age,
                'city' => $careGiver->profile?->city,
                'phone' => $careGiver->profile?->phone,
                'average_rating' => $reviews->isEmpty()
                    ? null
                    : round((float) $reviews->avg('rating'), 1),
                'review_count' => $reviews->count(),
                'reviews' => $reviews->map(fn ($review) => [
                    'id' => $review->id,
                    'reviewer_name' => $review->reviewer->name,
                    'reviewer_avatar_url' => $review->reviewer->avatar_url,
                    'rating' => $review->rating,
                    'comment' => $review->comment,
                    'created_at' => $review->created_at,
                ]),
            ],
        ]);
    }
}
