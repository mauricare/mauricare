<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CareBooking>
 */
class CareBookingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'care_giver_id' => null,
            'scheduled_date' => now()->addWeek()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration_hours' => 1,
            'care_type' => 'nursing_care',
            'description' => 'Post-surgery wound dressing and general assistance.',
            'preferred_carer_type' => 'nurse',
            'address' => '12 Royal Road, Port Louis',
            'contact_phone' => '57000000',
            'status' => BookingStatus::Open,
        ];
    }

    public function assigned(?User $careGiver = null): static
    {
        return $this->state([
            'care_giver_id' => $careGiver?->id ?? User::factory(),
            'status' => BookingStatus::Assigned,
        ]);
    }

    public function awaitingPayment(?User $careGiver = null): static
    {
        return $this->assigned($careGiver)->state([
            'status' => BookingStatus::AwaitingPayment,
            'amount_due' => 1500,
        ]);
    }

    public function paid(?User $careGiver = null): static
    {
        return $this->awaitingPayment($careGiver)->state([
            'status' => BookingStatus::Paid,
            'amount_paid' => 1500,
            'payment_method' => PaymentMethod::Juice,
            'payment_reference' => 'JUICE-123456',
        ]);
    }

    public function closed(?User $careGiver = null): static
    {
        return $this->paid($careGiver)->state(['status' => BookingStatus::Closed]);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => BookingStatus::Cancelled]);
    }
}
