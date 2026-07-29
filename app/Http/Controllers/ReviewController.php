<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\CareBooking;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewController extends Controller
{
    public function received(Request $request): JsonResponse
    {
        $user = $request->user();

        abort_unless(
            $user->hasRole('care_giver') || $user->careGiverProfile()->exists(),
            403,
            'Only care givers can view received reviews.',
        );

        $reviews = $user->reviewsReceived()
            ->with('reviewer.media')
            ->latest('id')
            ->get(['id', 'reviewer_id', 'rating', 'comment', 'created_at']);

        return response()->json([
            'data' => [
                'average_rating' => $reviews->isEmpty()
                    ? null
                    : round((float) $reviews->avg('rating'), 1),
                'review_count' => $reviews->count(),
                'reviews' => $reviews->map(fn (Review $review) => [
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

    public function store(StoreReviewRequest $request, CareBooking $careBooking): JsonResponse
    {
        Gate::authorize('review', $careBooking);

        $review = Review::create([
            'care_booking_id' => $careBooking->id,
            'reviewer_id' => $request->user()->id,
            'reviewee_id' => $careBooking->care_giver_id,
            ...$request->validated(),
        ]);

        return response()->json(['data' => $review], 201);
    }

    public function update(StoreReviewRequest $request, Review $review): JsonResponse
    {
        Gate::authorize('update', $review);

        $review->update($request->validated());

        return response()->json([
            'message' => 'Review updated successfully.',
            'data' => $review->fresh(),
        ]);
    }

    public function destroy(Request $request, Review $review): JsonResponse
    {
        Gate::authorize('delete', $review);

        $review->delete();

        return response()->json([], 204);
    }
}
