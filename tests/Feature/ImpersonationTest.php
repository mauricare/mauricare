<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_for_users_to_impersonate(): void
    {
        $admin = $this->userWithRole('admin');
        $careSeeker = $this->userWithRole('care_seeker', [
            'name' => 'Searchable Person',
            'email' => 'searchable@example.com',
        ]);
        $this->userWithRole('care_giver', [
            'name' => 'Different Person',
            'email' => 'different@example.com',
        ]);

        $this->actingAs($admin)
            ->getJson(route('impersonation.users', ['search' => 'searchable']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $careSeeker->id)
            ->assertJsonPath('data.0.roles.0', 'care_seeker');
    }

    public function test_non_admin_cannot_search_for_or_impersonate_users(): void
    {
        $user = $this->userWithRole('care_seeker');
        $target = $this->userWithRole('care_giver');

        $this->actingAs($user)
            ->getJson(route('impersonation.users'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('impersonation.start', $target))
            ->assertForbidden();

        $this->assertAuthenticatedAs($user);
    }

    public function test_admin_can_impersonate_a_user_and_return_to_the_admin_account(): void
    {
        $admin = $this->userWithRole('admin');
        $target = $this->userWithRole('care_seeker');

        $this->actingAs($admin)
            ->post(route('impersonation.start', $target))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertSame($admin->id, session('impersonator_id'));
        $this->assertSame($target->id, session('impersonated_user_id'));

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('auth.user.id', $target->id)
                ->where('auth.impersonation.active', true)
                ->where('auth.impersonation.administrator.id', $admin->id));

        $this->getJson(route('api.notifications.index'))
            ->assertOk();

        $this->post(route('impersonation.stop'))
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNull(session('impersonator_id'));
        $this->assertNull(session('impersonated_user_id'));
    }

    public function test_nested_impersonation_is_rejected(): void
    {
        $admin = $this->userWithRole('admin');
        $secondAdmin = $this->userWithRole('admin');
        $target = $this->userWithRole('care_seeker');

        $this->actingAs($admin)
            ->post(route('impersonation.start', $secondAdmin))
            ->assertRedirect(route('dashboard'));

        $this->post(route('impersonation.start', $target))
            ->assertStatus(409);

        $this->assertAuthenticatedAs($admin);
        $this->assertSame($admin->id, session('impersonator_id'));
        $this->assertSame($secondAdmin->id, session('impersonated_user_id'));
    }

    public function test_impersonated_care_giver_can_accept_an_open_booking(): void
    {
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        $booking = CareBooking::factory()->create();

        $this->actingAs($admin)
            ->post(route('impersonation.start', $careGiver))
            ->assertRedirect(route('dashboard'));

        $this->withHeader('Referer', config('app.url'))
            ->postJson(route('care-bookings.search'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $booking->id);

        $this->withHeader('Referer', config('app.url'))
            ->postJson(route('api.care-bookings.assign', $booking))
            ->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $booking->id,
            'care_giver_id' => $careGiver->id,
            'status' => 'assigned',
        ]);
    }

    public function test_impersonated_care_seeker_can_create_a_booking(): void
    {
        $admin = $this->userWithRole('admin');
        $careSeeker = $this->userWithRole('care_seeker');

        $this->actingAs($admin)
            ->post(route('impersonation.start', $careSeeker))
            ->assertRedirect(route('dashboard'));

        $this->withHeader('Referer', config('app.url'))
            ->postJson(route('care-bookings.mutate'), [
                'mutate' => [
                    [
                        'operation' => 'create',
                        'attributes' => [
                            'scheduled_date' => now()->addWeek()->format('Y-m-d'),
                            'start_time' => '10:00',
                            'duration_hours' => 1,
                            'care_type' => 'physiotherapy',
                            'description' => 'Created while impersonating a care seeker.',
                            'preferred_carer_type' => 'physiotherapist',
                            'address' => 'Test address',
                            'contact_phone' => '58880000',
                        ],
                    ],
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('care_bookings', [
            'user_id' => $careSeeker->id,
            'care_type' => 'physiotherapy',
            'status' => 'open',
        ]);
    }

    public function test_user_cannot_end_an_impersonation_that_is_not_active(): void
    {
        $user = $this->userWithRole('care_seeker');

        $this->actingAs($user)
            ->post(route('impersonation.stop'))
            ->assertForbidden();

        $this->assertAuthenticatedAs($user);
    }

    private function userWithRole(string $role, array $attributes = []): User
    {
        Role::findOrCreate($role);
        $user = User::factory()->create($attributes);
        $user->assignRole($role);

        if ($role === 'care_seeker') {
            $user->careSeekerProfile()->create(['is_active' => true]);
        }

        if ($role === 'care_giver') {
            $user->careGiverProfile()->create([
                'type' => 'nurse',
                'is_active' => true,
            ]);
        }

        return $user;
    }
}
