<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CareBookingNotificationTest extends TestCase
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

    public function test_seeker_is_notified_when_a_care_giver_accepts_the_booking(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->for($seeker)->create();

        $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/assign")->assertOk();

        $notification = $seeker->notifications()->first();
        $this->assertNotNull($notification);
        $this->assertStringContainsString("{$careGiver->name} has accepted your booking", $notification->data['message']);
        $this->assertSame($booking->id, $notification->data['care_booking_id']);
    }

    public function test_seeker_is_notified_when_the_visit_is_completed(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/complete-visit", ['amount_due' => 1800])
            ->assertOk();

        $this->assertStringContainsString(
            'Amount to pay: Rs 1800',
            $seeker->notifications()->first()->data['message'],
        );
    }

    public function test_care_giver_is_notified_when_the_seeker_confirms_payment(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->awaitingPayment($careGiver)->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/confirm-payment", [
                'amount_paid' => 1800,
                'payment_method' => 'cash',
            ])
            ->assertOk();

        $this->assertStringContainsString(
            "{$seeker->name} recorded a payment of Rs 1800",
            $careGiver->notifications()->first()->data['message'],
        );
    }

    public function test_seeker_is_notified_when_the_booking_is_closed(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->paid($careGiver)->for($seeker)->create();

        $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/close")->assertOk();

        $this->assertStringContainsString('is now closed', $seeker->notifications()->first()->data['message']);
    }

    public function test_care_giver_is_notified_when_the_seeker_cancels_an_assigned_booking(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $this->actingAs($seeker)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                ['operation' => 'update', 'key' => $booking->id, 'attributes' => ['status' => 'cancelled']],
            ],
        ])->assertOk();

        $this->assertStringContainsString('cancelled the booking', $careGiver->notifications()->first()->data['message']);
    }

    public function test_nobody_is_notified_when_an_open_booking_is_cancelled(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->for($seeker)->create();

        $this->actingAs($seeker)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                ['operation' => 'update', 'key' => $booking->id, 'attributes' => ['status' => 'cancelled']],
            ],
        ])->assertOk();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_care_giver_is_notified_when_booking_details_change(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $this->actingAs($seeker)->postJson('/api/care-bookings/mutate', [
            'mutate' => [
                ['operation' => 'update', 'key' => $booking->id, 'attributes' => ['start_time' => '14:00']],
            ],
        ])->assertOk();

        $this->assertStringContainsString('updated the booking', $careGiver->notifications()->first()->data['message']);
    }

    public function test_notifications_endpoint_returns_notifications_and_unread_count(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->for($seeker)->create();
        $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/assign")->assertOk();

        $response = $this->actingAs($seeker)->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonCount(1, 'data');
        $this->assertStringContainsString('has accepted your booking', $response->json('data.0.message'));
    }

    public function test_mark_all_read_clears_the_unread_count(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->for($seeker)->create();
        $this->actingAs($careGiver)->postJson("/api/care-bookings/{$booking->id}/assign")->assertOk();

        $this->actingAs($seeker)->postJson('/api/notifications/mark-all-read')->assertOk();

        $this->actingAs($seeker)
            ->getJson('/api/notifications')
            ->assertJsonPath('unread_count', 0);
    }
}
