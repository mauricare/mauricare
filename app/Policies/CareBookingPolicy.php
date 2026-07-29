<?php

namespace App\Policies;

use App\Enums\BookingStatus;
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
        return $careBooking->user_id === $user->id
            || $careBooking->care_giver_id === $user->id
            || ($careBooking->status === BookingStatus::Open && $this->isActiveCareGiver($user));
    }

    public function create(User $user): bool
    {
        return $user->hasRole('care_seeker')
            && (bool) $user->careSeekerProfile?->is_active;
    }

    public function update(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->user_id === $user->id
            && $careBooking->status->seekerCanModify();
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

    public function assign(User $user, CareBooking $careBooking): bool
    {
        return $this->isActiveCareGiver($user)
            && $careBooking->user_id !== $user->id
            && $careBooking->care_giver_id === null
            && $careBooking->status === BookingStatus::Open;
    }

    public function completeVisit(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->care_giver_id === $user->id
            && $careBooking->status === BookingStatus::Assigned;
    }

    public function confirmPayment(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->user_id === $user->id
            && $careBooking->status === BookingStatus::AwaitingPayment;
    }

    public function close(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->care_giver_id === $user->id
            && $careBooking->status === BookingStatus::Paid;
    }

    public function review(User $user, CareBooking $careBooking): bool
    {
        return $careBooking->user_id === $user->id
            && $careBooking->care_giver_id !== null
            && $careBooking->status === BookingStatus::Closed
            && ! $careBooking->review()->exists();
    }

    public function attachUser(User $user, CareBooking $careBooking): bool
    {
        return false;
    }

    public function detachUser(User $user, CareBooking $careBooking): bool
    {
        return false;
    }

    private function isActiveCareGiver(User $user): bool
    {
        return $user->hasRole('care_giver')
            && (bool) $user->careGiverProfile?->is_active;
    }
}
