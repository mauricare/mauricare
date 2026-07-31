<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    private function fakeAvatar(string $name = 'avatar.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent(
            $name,
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
        );
    }

    public function test_user_can_upload_and_replace_their_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => $this->fakeAvatar('first-avatar.png'),
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertCount(1, $user->getMedia('avatar'));
        Storage::disk('public')->assertExists($user->getFirstMedia('avatar')->getPathRelativeToRoot());

        $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => $this->fakeAvatar('replacement.png'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertCount(1, $user->refresh()->getMedia('avatar'));
        $this->assertSame('replacement.png', $user->getFirstMedia('avatar')->file_name);
    }

    public function test_avatar_must_be_a_supported_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/profile/avatar', [
                'avatar' => UploadedFile::fake()->create('avatar.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasErrors('avatar');

        $this->assertDatabaseCount('media', 0);
    }

    public function test_user_can_remove_their_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->addMedia($this->fakeAvatar())
            ->toMediaCollection('avatar');

        $this->actingAs($user)
            ->delete('/profile/avatar')
            ->assertRedirect('/profile');

        $this->assertDatabaseCount('media', 0);
    }

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
                'phone' => '57000000',
                'age' => 42,
                'address' => '12 Royal Road',
                'city' => 'Curepipe',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '57000000',
            'age' => 42,
            'city' => 'Curepipe',
        ]);
    }

    public function test_care_seeker_can_update_registration_specific_profile_fields(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_seeker', 'web'));

        $this->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Marie',
                'last_name' => 'Paul',
                'email' => $user->email,
                'age' => 64,
                'phone' => '57000000',
                'address' => '12 Royal Road',
                'city' => 'Curepipe',
                'care_for' => 'Myself',
                'care_needs' => 'Daily mobility assistance',
                'preferred_contact_method' => 'phone',
                'emergency_contact_name' => 'Jean Paul',
                'emergency_contact_phone' => '58000000',
                'mobility_level' => 'Assisted',
                'medical_notes' => 'Penicillin allergy',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('care_seeker_profiles', [
            'user_id' => $user->id,
            'care_for' => 'Myself',
            'preferred_contact_method' => 'phone',
            'mobility_level' => 'Assisted',
        ]);
    }

    public function test_care_giver_can_update_type_and_replace_cv(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_giver', 'web'));
        $user->careGiverProfile()->create(['type' => 'nurse']);

        $this->actingAs($user)
            ->post('/profile', [
                '_method' => 'patch',
                'first_name' => 'Asha',
                'last_name' => 'Devi',
                'email' => $user->email,
                'age' => 35,
                'phone' => '57000000',
                'city' => 'Vacoas',
                'care_giver_type' => 'physiotherapist',
                'cv' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('care_giver_profiles', [
            'user_id' => $user->id,
            'type' => 'physiotherapist',
        ]);
        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'type' => 'cv',
            'original_name' => 'cv.pdf',
        ]);
    }

    public function test_agency_can_update_all_registration_profile_fields(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('agency', 'web'));
        $user->agencyProfile()->create([
            'agency_name' => 'Old Agency',
            'contact_person' => 'Old Contact',
        ]);

        $this->actingAs($user)
            ->post('/profile', [
                '_method' => 'patch',
                'email' => $user->email,
                'phone' => '59000000',
                'agency_name' => 'Island Care Ltd',
                'contact_person' => 'Anita Devi',
                'agency_address' => '8 Coastal Road',
                'services_offered' => 'Nursing and respite care',
                'agency_license' => UploadedFile::fake()->create('license.pdf', 100, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Island Care Ltd', $user->refresh()->name);
        $this->assertDatabaseHas('agency_profiles', [
            'user_id' => $user->id,
            'agency_name' => 'Island Care Ltd',
            'contact_person' => 'Anita Devi',
        ]);
        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'type' => 'agency_license',
            'original_name' => 'license.pdf',
        ]);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $user->email,
                'phone' => '57000000',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertSoftDeleted($user);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
