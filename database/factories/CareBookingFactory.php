<?php

namespace Database\Factories;

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
            'scheduled_date' => now()->addWeek()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration_hours' => 1,
            'care_type' => 'nursing_care',
            'description' => 'Post-surgery wound dressing and general assistance.',
            'preferred_carer_type' => 'nurse',
            'status' => 'pending',
        ];
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }

    public function cancelled(): static
    {
        return $this->state(['status' => 'cancelled']);
    }
}
