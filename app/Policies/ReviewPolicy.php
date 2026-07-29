<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id
            || $review->reviewee_id === $user->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id;
    }

    public function delete(User $user, Review $review): bool
    {
        return $review->reviewer_id === $user->id;
    }

    public function restore(User $user, Review $review): bool
    {
        return false;
    }

    public function forceDelete(User $user, Review $review): bool
    {
        return false;
    }
}
