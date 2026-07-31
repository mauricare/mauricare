<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CareBookingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function addAvatar(User $user): void
    {
        Storage::fake('public');

        $user->addMedia(UploadedFile::fake()->createWithContent(
            'avatar.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        ))->toMediaCollection('avatar');
    }

    private function careSeeker(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_seeker', 'web'));
        $user->careSeekerProfile()->create(['is_active' => true]);

        return $user;
    }

    private function careGiver(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_giver', 'web'));
        $user->careGiverProfile()->create([
            'type' => 'nurse',
            'is_active' => true,
        ]);

        return $user;
    }

    private function inactiveCareGiver(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_giver', 'web'));
        $user->careGiverProfile()->create([
            'type' => 'nurse',
            'is_active' => false,
        ]);

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

    public function test_inactive_care_giver_cannot_see_open_requests(): void
    {
        $careGiver = $this->inactiveCareGiver();
        CareBooking::factory()->create();
        $ownAssignedBooking = CareBooking::factory()->assigned($careGiver)->create();

        $response = $this->actingAs($careGiver)->postJson('/api/care-bookings/search');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownAssignedBooking->id);
    }

    public function test_inactive_care_giver_cannot_assign_an_open_booking(): void
    {
        $careGiver = $this->inactiveCareGiver();
        $booking = CareBooking::factory()->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/assign")
            ->assertForbidden();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'care_giver_id' => null,
            'status' => 'open',
        ]);
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
        $this->addAvatar($booking->user);

        $response = $this->actingAs($careGiver)->postJson('/api/care-bookings/search', [
            'search' => [
                'includes' => [
                    ['relation' => 'user'],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.id', $booking->id)
            ->assertJsonPath('data.0.user.name', $booking->user->name)
            ->assertJsonPath('data.0.user.avatar_url', $booking->user->avatar_url);
    }

    public function test_care_seeker_search_includes_the_care_giver_details(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->assigned()->for($seeker)->create();
        $this->addAvatar($booking->careGiver);

        $response = $this->actingAs($seeker)->postJson('/api/care-bookings/search', [
            'search' => [
                'includes' => [
                    ['relation' => 'careGiver'],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('data.0.id', $booking->id)
            ->assertJsonPath('data.0.care_giver.name', $booking->careGiver->name)
            ->assertJsonPath('data.0.care_giver.avatar_url', $booking->careGiver->avatar_url);
    }

    public function test_care_seeker_can_review_the_care_giver_after_a_booking_is_closed(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/review", [
                'rating' => 5,
                'comment' => 'Kind, punctual, and very professional.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.rating', 5);

        $this->assertDatabaseHas('reviews', [
            'care_booking_id' => $booking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 5,
            'comment' => 'Kind, punctual, and very professional.',
        ]);
    }

    public function test_review_rating_must_be_between_one_and_five(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->closed()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/review", ['rating' => 6])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');
    }

    public function test_booking_cannot_be_reviewed_before_it_is_closed(): void
    {
        $seeker = $this->careSeeker();
        $booking = CareBooking::factory()->paid()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/review", ['rating' => 4])
            ->assertForbidden();
    }

    public function test_care_giver_cannot_review_the_booking(): void
    {
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->create();

        $this->actingAs($careGiver)
            ->postJson("/api/care-bookings/{$booking->id}/review", ['rating' => 4])
            ->assertForbidden();
    }

    public function test_booking_cannot_be_reviewed_twice(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();

        Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 5,
        ]);

        $this->actingAs($seeker)
            ->postJson("/api/care-bookings/{$booking->id}/review", ['rating' => 3])
            ->assertForbidden();

        $this->assertDatabaseCount('reviews', 1);
    }

    public function test_care_seeker_can_edit_their_review(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();
        $review = Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 3,
            'comment' => 'Good.',
        ]);

        $this->actingAs($seeker)
            ->patchJson("/api/reviews/{$review->id}", [
                'rating' => 5,
                'comment' => 'Excellent care.',
            ])
            ->assertOk()
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.comment', 'Excellent care.');

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Excellent care.',
        ]);
    }

    public function test_care_seeker_can_delete_their_review(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();
        $review = Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 4,
        ]);

        $this->actingAs($seeker)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertNoContent();

        $this->assertSoftDeleted($review);
    }

    public function test_care_seeker_cannot_edit_or_delete_another_users_review(): void
    {
        $reviewer = $this->careSeeker();
        $otherSeeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->for($reviewer)->create();
        $review = Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $reviewer->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 4,
        ]);

        $this->actingAs($otherSeeker)
            ->patchJson("/api/reviews/{$review->id}", ['rating' => 1])
            ->assertForbidden();

        $this->actingAs($otherSeeker)
            ->deleteJson("/api/reviews/{$review->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'rating' => 4]);
    }

    public function test_care_seeker_booking_search_includes_its_review(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $booking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();
        Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 4,
            'comment' => 'Very good care.',
        ]);

        $this->actingAs($seeker)
            ->postJson('/api/care-bookings/search', [
                'search' => [
                    'includes' => [
                        ['relation' => 'review'],
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.0.review.rating', 4)
            ->assertJsonPath('data.0.review.comment', 'Very good care.');
    }

    public function test_linked_care_seeker_can_view_care_giver_profile_and_all_reviews(): void
    {
        $seeker = $this->careSeeker();
        $otherSeeker = $this->careSeeker();
        $this->addAvatar($otherSeeker);
        $careGiver = $this->careGiver();
        $careGiver->profile()->create([
            'first_name' => 'Marie',
            'last_name' => 'Jean',
            'age' => 34,
            'phone' => '57001234',
            'city' => 'Curepipe',
        ]);
        $careGiver->careGiverProfile()->update(['type' => 'nurse']);

        $firstBooking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();
        $secondBooking = CareBooking::factory()->closed($careGiver)->for($otherSeeker)->create();

        Review::create([
            'care_booking_id' => $firstBooking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 5,
            'comment' => 'Excellent care.',
        ]);
        Review::create([
            'care_booking_id' => $secondBooking->id,
            'reviewer_id' => $otherSeeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 3,
            'comment' => 'Good overall.',
        ]);

        $this->actingAs($seeker)
            ->getJson("/api/care-givers/{$careGiver->id}/profile")
            ->assertOk()
            ->assertJsonPath('data.name', $careGiver->name)
            ->assertJsonPath('data.type', 'nurse')
            ->assertJsonPath('data.age', 34)
            ->assertJsonPath('data.city', 'Curepipe')
            ->assertJsonPath('data.phone', '57001234')
            ->assertJsonPath('data.average_rating', 4)
            ->assertJsonPath('data.review_count', 2)
            ->assertJsonPath('data.reviews.0.reviewer_name', $otherSeeker->name)
            ->assertJsonPath('data.reviews.0.reviewer_avatar_url', $otherSeeker->avatar_url)
            ->assertJsonPath('data.reviews.1.reviewer_name', $seeker->name)
            ->assertJsonCount(2, 'data.reviews');
    }

    public function test_unrelated_care_seeker_cannot_view_care_giver_profile(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();

        $this->actingAs($seeker)
            ->getJson("/api/care-givers/{$careGiver->id}/profile")
            ->assertForbidden();
    }

    public function test_care_giver_can_view_only_their_received_reviews(): void
    {
        $careGiver = $this->careGiver();
        $otherCareGiver = $this->careGiver();
        $seeker = $this->careSeeker();
        $otherSeeker = $this->careSeeker();
        $this->addAvatar($seeker);

        $firstBooking = CareBooking::factory()->closed($careGiver)->for($seeker)->create();
        $secondBooking = CareBooking::factory()->closed($careGiver)->for($otherSeeker)->create();
        $unrelatedBooking = CareBooking::factory()->closed($otherCareGiver)->for($seeker)->create();

        Review::create([
            'care_booking_id' => $firstBooking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 5,
            'comment' => 'Excellent.',
        ]);
        Review::create([
            'care_booking_id' => $secondBooking->id,
            'reviewer_id' => $otherSeeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 3,
            'comment' => 'Good.',
        ]);
        Review::create([
            'care_booking_id' => $unrelatedBooking->id,
            'reviewer_id' => $seeker->id,
            'reviewee_id' => $otherCareGiver->id,
            'rating' => 1,
        ]);

        $this->actingAs($careGiver)
            ->getJson('/api/reviews/received')
            ->assertOk()
            ->assertJsonPath('data.average_rating', 4)
            ->assertJsonPath('data.review_count', 2)
            ->assertJsonPath('data.reviews.1.reviewer_name', $seeker->name)
            ->assertJsonPath('data.reviews.1.reviewer_avatar_url', $seeker->avatar_url)
            ->assertJsonCount(2, 'data.reviews');
    }

    public function test_care_seeker_cannot_access_received_reviews_dashboard_data(): void
    {
        $this->actingAs($this->careSeeker())
            ->getJson('/api/reviews/received')
            ->assertForbidden();
    }
}
