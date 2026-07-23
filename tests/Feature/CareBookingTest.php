<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CareBookingTest extends TestCase
{
    use RefreshDatabase;

    private function careSeeker(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_seeker', 'web'));

        return $user;
    }

    private function validAttributes(): array
    {
        return [
            'scheduled_date' => now()->addWeek()->format('Y-m-d'),
            'start_time' => '10:00',
            'duration_hours' => 1,
            'care_type' => 'physiotherapy',
            'description' => 'Knee rehabilitation after surgery.',
            'preferred_carer_type' => 'physiotherapist',
        ];
    }

    public function test_guest_cannot_access_care_bookings(): void
    {
        $this->postJson('/api/care-bookings/search')->assertUnauthorized();
    }

    public function test_care_seeker_can_create_a_booking(): void
    {
        $user = $this->careSeeker();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'create',
                    'attributes' => $this->validAttributes(),
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'user_id' => $user->id,
            'care_type' => 'physiotherapy',
            'status' => 'pending',
        ]);
    }

    public function test_user_without_care_seeker_role_cannot_create_a_booking(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'create',
                    'attributes' => $this->validAttributes(),
                ],
            ],
        ]);

        $response->assertForbidden();
    }

    public function test_search_only_returns_own_bookings(): void
    {
        $user = $this->careSeeker();
        $ownBooking = CareBooking::factory()->for($user)->create();
        CareBooking::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/search');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownBooking->id);
    }

    public function test_care_seeker_can_update_a_pending_booking(): void
    {
        $user = $this->careSeeker();
        $booking = CareBooking::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'update',
                    'key' => $booking->id,
                    'attributes' => ['description' => 'Updated care instructions.'],
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'description' => 'Updated care instructions.',
        ]);
    }

    public function test_care_seeker_can_cancel_a_booking(): void
    {
        $user = $this->careSeeker();
        $booking = CareBooking::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'update',
                    'key' => $booking->id,
                    'attributes' => ['status' => 'cancelled'],
                ],
            ],
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_care_seeker_cannot_set_a_status_other_than_cancelled(): void
    {
        $user = $this->careSeeker();
        $booking = CareBooking::factory()->for($user)->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'update',
                    'key' => $booking->id,
                    'attributes' => ['status' => 'completed'],
                ],
            ],
        ]);

        $response->assertUnprocessable();
    }

    public function test_completed_booking_cannot_be_updated(): void
    {
        $user = $this->careSeeker();
        $booking = CareBooking::factory()->for($user)->completed()->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'update',
                    'key' => $booking->id,
                    'attributes' => ['description' => 'Should not change.'],
                ],
            ],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'description' => $booking->description,
        ]);
    }

    public function test_care_seeker_cannot_update_someone_elses_booking(): void
    {
        $user = $this->careSeeker();
        $otherBooking = CareBooking::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                [
                    'operation' => 'update',
                    'key' => $otherBooking->id,
                    'attributes' => ['description' => 'Hijacked.'],
                ],
            ],
        ]);

        $response->assertForbidden();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $otherBooking->id,
            'description' => $otherBooking->description,
        ]);
    }
}
