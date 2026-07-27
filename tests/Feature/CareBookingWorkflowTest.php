<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CareBookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function careSeeker(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_seeker', 'web'));

        return $user;
    }

    private function careGiver(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_giver', 'web'));

        return $user;
    }

    public function test_care_giver_search_returns_open_and_own_assigned_bookings(): void
    {
        $careGiver = $this->careGiver();
        $openBooking = CareBooking::factory()->create();
        $ownAssignedBooking = CareBooking::factory()->assigned($careGiver)->create();
        CareBooking::factory()->assigned()->create();
        CareBooking::factory()->cancelled()->create();

        $response = $this->actingAs($careGiver)->postJson('/api/care-bookings/search');

        $response->assertOk()->assertJsonCount(2, 'data');

        $returnedIds = collect($response->json('data'))->pluck('id');
        $this->assertEqualsCanonicalizing(
            [$openBooking->id, $ownAssignedBooking->id],
            $returnedIds->all(),
        );
    }

    public function test_care_giver_can_assign_himself_to_an_open_booking(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->create();

        $response = $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/assign");

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'care_giver_id' => $careGiver->id,
            'status' => 'assigned',
        ]);
    }

    public function test_care_seeker_cannot_assign_himself_to_a_booking(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/assign")
            ->assertForbidden();
    }

    public function test_care_giver_cannot_assign_an_already_assigned_booking(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->assigned()->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/assign")
            ->assertForbidden();
    }

    public function test_assigned_care_giver_can_mark_the_visit_completed(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->assigned($careGiver)->create();

        $response = $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/complete-visit", [
            'amount_due' => 1800.50,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'status' => 'awaiting_payment',
            'amount_due' => 1800.50,
        ]);
    }

    public function test_completing_the_visit_requires_an_amount(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->assigned($careGiver)->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/complete-visit")
            ->assertUnprocessable();
    }

    public function test_another_care_giver_cannot_complete_the_visit(): void
    {
        $booking = CareBooking::factory()->assigned()->create();

        $this->actingAs($this->careGiver())
            ->postJson("/api/care-bookings/{$booking->id}/complete-visit", ['amount_due' => 1000])
            ->assertForbidden();
    }

    public function test_visit_cannot_be_completed_before_assignment(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/complete-visit", ['amount_due' => 1000])
            ->assertForbidden();
    }

    public function test_care_seeker_can_confirm_the_payment(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->awaitingPayment()->for($seeker)->create();

        $response = $this->actingAs($seeker)->postJson("/api/care-bookings/{$booking->id}/confirm-payment", [
            'amount_paid' => 1500,
            'payment_method' => 'juice',
            'payment_reference' => 'JUICE-987654',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'status' => 'paid',
            'amount_paid' => 1500,
            'payment_method' => 'juice',
            'payment_reference' => 'JUICE-987654',
        ]);
    }

    public function test_juice_payment_requires_a_transaction_reference(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->awaitingPayment()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/confirm-payment", [
                'amount_paid' => 1500,
                'payment_method' => 'juice',
            ])
            ->assertUnprocessable();
    }

    public function test_cash_payment_does_not_require_a_transaction_reference(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->awaitingPayment()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/confirm-payment", [
                'amount_paid' => 1500,
                'payment_method' => 'cash',
            ])
            ->assertOk();
    }

    public function test_care_giver_cannot_confirm_the_payment(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->awaitingPayment($careGiver)->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/confirm-payment", [
                'amount_paid' => 1500,
                'payment_method' => 'cash',
            ])
            ->assertForbidden();
    }

    public function test_payment_cannot_be_confirmed_before_the_visit_is_completed(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->assigned()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/confirm-payment", [
                'amount_paid' => 1500,
                'payment_method' => 'cash',
            ])
            ->assertForbidden();
    }

    public function test_care_giver_can_close_a_paid_booking(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->paid($careGiver)->create();

        $response = $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/close");

        $response->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'status' => 'closed',
        ]);
    }

    public function test_care_seeker_cannot_close_a_booking(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->paid()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/close")
            ->assertForbidden();
    }

    public function test_booking_cannot_be_closed_before_payment(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->awaitingPayment($careGiver)->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/close")
            ->assertForbidden();
    }

    public function test_care_seeker_cannot_cancel_after_the_visit_is_completed(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->awaitingPayment()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson('/api/care-bookings/mutate', [
                'mutate' => [
                    [
                        'operation' => 'update',
                        'key' => $booking->id,
                        'attributes' => ['status' => 'cancelled'],
                    ],
                ],
            ])
            ->assertForbidden();
    }

    public function test_care_giver_search_includes_the_care_seeker_details(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->create();

        $response = $this->actingAs($careGiver)->postJson('/api/care-bookings/search', [
            'search' => [
                'includes' => [
                    ['relation' => 'user'],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.id', $booking->id)
            ->assertJsonPath('data.0.user.name', $booking->user->name);
    }

    public function test_care_seeker_search_includes_the_care_giver_details(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->assigned()->for($seeker)->create();

        $response = $this->actingAs($seeker)->postJson('/api/care-bookings/search', [
            'search' => [
                'includes' => [
                    ['relation' => 'careGiver'],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.id', $booking->id)
            ->assertJsonPath('data.0.care_giver.name', $booking->careGiver->name);
    }
}
