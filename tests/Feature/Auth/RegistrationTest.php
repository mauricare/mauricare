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
            'care_needs' => 'Home care support',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }
}
