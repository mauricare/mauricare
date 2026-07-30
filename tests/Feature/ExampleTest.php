<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_homepage_testimonials_are_fed_from_care_seeker_reviews(): void
    {
        $careSeeker = User::factory()->create(['name' => 'Marie Dupont']);
        $careGiver = User::factory()->create();
        $booking = CareBooking::factory()
            ->for($careSeeker)
            ->closed($careGiver)
            ->create(['care_type' => 'nursing_care']);

        $review = Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $careSeeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 4,
            'comment' => 'Professional and compassionate care.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('testimonials', 1)
                ->where('testimonials.0.id', $review->id)
                ->where('testimonials.0.name', 'Marie D.')
                ->where('testimonials.0.initials', 'MD')
                ->where('testimonials.0.rating', 4)
                ->where('testimonials.0.text', 'Professional and compassionate care.')
                ->where('testimonials.0.role', 'Nursing Care client'));
    }

    public function test_homepage_excludes_reviews_without_comments(): void
    {
        $careSeeker = User::factory()->create();
        $careGiver = User::factory()->create();
        $booking = CareBooking::factory()
            ->for($careSeeker)
            ->closed($careGiver)
            ->create();

        Review::create([
            'care_booking_id' => $booking->id,
            'reviewer_id' => $careSeeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 5,
            'comment' => null,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->has('testimonials', 0));
    }
}
