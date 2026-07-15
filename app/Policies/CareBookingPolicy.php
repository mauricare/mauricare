<?php

namespace App\Policies;

use App\Models\CareBooking;
use App\Models\User;

class CareBookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('care_seeker') || $user->careSeekerProfile()->exists();
    }

    public function update(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->user_id === $user->id
            && ! in_array($careBooking->status, ['completed', 'cancelled'], true);
    }

    public function delete(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->user_id === $user->id;
    }

    public function restore(User $user, CareBooking $careBooking): bool
    {
        return false;
    }

    public function forceDelete(User $user, CareBooking $careBooking): bool
    {
        return false;
    }
}
