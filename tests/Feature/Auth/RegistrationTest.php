<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'role' => 'care_seeker',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'age' => 35,
            'phone' => '+230 5555 0000',
            'city' => 'Port Louis',
            'care_for' => 'Myself',
            'password' => 'SafePassword1!',
            'password_confirmation' => 'SafePassword1!',
            'privacy_notice_version' => config('privacy.notice_version'),
            'privacy_notice_accepted' => true,
            'terms_version' => config('terms.version'),
            'terms_accepted' => true,
            'health_data_consent' => true,
            'data_subject_authority_confirmed' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('privacy_acceptances', [
            'notice_version' => config('privacy.notice_version'),
            'terms_version' => config('terms.version'),
        ]);
    }

    public function test_registration_requires_current_terms_privacy_acknowledgement_and_health_consent(): void
    {
        $this->post('/register', [
            'role' => 'care_seeker', 'first_name' => 'Test', 'last_name' => 'User',
            'email' => 'privacy@example.com', 'age' => 35, 'phone' => '5555', 'city' => 'Moka',
            'care_for' => 'Myself', 'care_needs' => 'Support', 'password' => 'password',
            'password_confirmation' => 'password', 'privacy_notice_version' => 'outdated',
            'terms_version' => 'outdated',
        ])->assertSessionHasErrors(['privacy_notice_version', 'privacy_notice_accepted', 'terms_version', 'terms_accepted', 'health_data_consent', 'data_subject_authority_confirmed']);

        $this->assertDatabaseMissing('users', ['email' => 'privacy@example.com']);
    }

    public function test_registration_rejects_an_unsafe_password(): void
    {
        $this->post('/register', [
            'role' => 'care_giver',
            'first_name' => 'Test',
            'last_name' => 'Provider',
            'email' => 'weak-password@example.com',
            'age' => 35,
            'phone' => '5555',
            'city' => 'Moka',
            'care_giver_type' => 'nurse',
            'password' => 'password',
            'password_confirmation' => 'password',
            'privacy_notice_version' => config('privacy.notice_version'),
            'privacy_notice_accepted' => true,
            'terms_version' => config('terms.version'),
            'terms_accepted' => true,
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'weak-password@example.com']);
    }
}
