<?php

namespace Tests\Feature;

use App\Models\CareBooking;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MessagingTest extends TestCase
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

        return $user;
    }

    private function careGiver(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('care_giver', 'web'));

        return $user;
    }

    private function admin(): User
    {
        $user = User::factory()->create(['name' => 'Mauricare Admin']);
        $user->assignRole(Role::findOrCreate('admin', 'web'));

        return $user;
    }

    public function test_guest_cannot_access_messages(): void
    {
        $this->getJson('/api/messages/contacts')->assertUnauthorized();
    }

    public function test_seeker_contacts_lists_care_givers_assigned_to_their_bookings(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $this->addAvatar($careGiver);
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();
        CareBooking::factory()->assigned()->create();

        $response = $this->actingAs($seeker)->getJson('/api/messages/contacts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $careGiver->id)
            ->assertJsonPath('data.0.name', $careGiver->name)
            ->assertJsonPath('data.0.avatar_url', $careGiver->avatar_url);
    }

    public function test_care_giver_contacts_lists_seekers_from_their_assigned_bookings(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $this->addAvatar($seeker);
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $response = $this->actingAs($careGiver)->getJson('/api/messages/contacts');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $seeker->id)
            ->assertJsonPath('data.0.avatar_url', $seeker->avatar_url);
    }

    public function test_admin_is_a_messaging_contact_for_care_seekers_and_care_givers(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();
        $careGiver = $this->careGiver();

        $this->actingAs($careSeeker)
            ->getJson('/api/messages/contacts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $admin->id)
            ->assertJsonPath('data.0.is_admin', true);

        $this->actingAs($careGiver)
            ->getJson('/api/messages/contacts')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $admin->id)
            ->assertJsonPath('data.0.is_admin', true);
    }

    public function test_care_users_and_admin_can_exchange_messages_without_a_booking(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();

        $this->actingAs($careSeeker)
            ->postJson("/api/messages/{$admin->id}", ['body' => 'I need help.'])
            ->assertCreated();

        $this->actingAs($admin)
            ->postJson("/api/messages/{$careSeeker->id}", ['body' => 'How can we assist?'])
            ->assertCreated();

        $this->actingAs($careSeeker)
            ->getJson("/api/messages/{$admin->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.body', 'I need help.')
            ->assertJsonPath('data.1.body', 'How can we assist?');
    }

    public function test_seeker_can_message_an_assigned_care_giver(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $response = $this->actingAs($seeker)->postJson("/api/messages/{$careGiver->id}", [
            'body' => 'Hello, what time will you arrive?',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('messages', [
            'sender_id' => $seeker->id,
            'recipient_id' => $careGiver->id,
            'body' => 'Hello, what time will you arrive?',
        ]);
    }

    public function test_care_giver_can_reply(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $this->actingAs($careGiver)
            ->postJson("/api/messages/{$seeker->id}", ['body' => 'I will be there at 9am.'])
            ->assertCreated();
    }

    public function test_seeker_cannot_message_an_unrelated_care_giver(): void
    {
        $seeker = $this->careSeeker();
        $otherCareGiver = $this->careGiver();
        CareBooking::factory()->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/messages/{$otherCareGiver->id}", ['body' => 'Hello?'])
            ->assertForbidden();
    }

    public function test_message_body_is_required(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        $this->actingAs($seeker)
            ->postJson("/api/messages/{$careGiver->id}", ['body' => ''])
            ->assertUnprocessable();
    }

    public function test_conversation_returns_messages_from_both_sides_and_marks_them_read(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        Message::create(['sender_id' => $seeker->id, 'recipient_id' => $careGiver->id, 'body' => 'Hello!']);
        Message::create(['sender_id' => $careGiver->id, 'recipient_id' => $seeker->id, 'body' => 'Hi, how can I help?']);

        $response = $this->actingAs($seeker)->getJson("/api/messages/{$careGiver->id}");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.body', 'Hello!')
            ->assertJsonPath('data.1.body', 'Hi, how can I help?');

        $this->assertDatabaseMissing('messages', [
            'sender_id' => $careGiver->id,
            'recipient_id' => $seeker->id,
            'read_at' => null,
        ]);
    }

    public function test_contacts_include_unread_counts(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        Message::create(['sender_id' => $careGiver->id, 'recipient_id' => $seeker->id, 'body' => 'One']);
        Message::create(['sender_id' => $careGiver->id, 'recipient_id' => $seeker->id, 'body' => 'Two']);

        $response = $this->actingAs($seeker)->getJson('/api/messages/contacts');

        $response->assertOk()
            ->assertJsonPath('data.0.unread_count', 2)
            ->assertJsonPath('data.0.last_message.body', 'Two');
    }

    public function test_unread_count_reflects_unread_messages_and_resets_after_reading(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->assigned($careGiver)->for($seeker)->create();

        Message::create(['sender_id' => $careGiver->id, 'recipient_id' => $seeker->id, 'body' => 'One']);
        Message::create(['sender_id' => $careGiver->id, 'recipient_id' => $seeker->id, 'body' => 'Two']);

        $this->actingAs($seeker)
            ->getJson('/api/messages/unread-count')
            ->assertOk()
            ->assertJsonPath('count', 2);

        $this->actingAs($seeker)->getJson("/api/messages/{$careGiver->id}");

        $this->actingAs($seeker)
            ->getJson('/api/messages/unread-count')
            ->assertOk()
            ->assertJsonPath('count', 0);
    }

    public function test_conversation_with_unrelated_user_is_forbidden(): void
    {
        $seeker = $this->careSeeker();
        $stranger = User::factory()->create();

        $this->actingAs($seeker)
            ->getJson("/api/messages/{$stranger->id}")
            ->assertForbidden();
    }

    public function test_sender_can_edit_their_message(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $message = Message::create([
            'sender_id' => $seeker->id,
            'recipient_id' => $careGiver->id,
            'body' => 'Original message',
        ]);

        $this->actingAs($seeker)
            ->patchJson("/api/messages/{$message->id}", ['body' => 'Updated message'])
            ->assertOk()
            ->assertJsonPath('data.body', 'Updated message');

        $this->assertDatabaseHas('messages', [
            'id' => $message->id,
            'body' => 'Updated message',
        ]);
    }

    public function test_recipient_cannot_edit_a_message(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $message = Message::create([
            'sender_id' => $seeker->id,
            'recipient_id' => $careGiver->id,
            'body' => 'Original message',
        ]);

        $this->actingAs($careGiver)
            ->patchJson("/api/messages/{$message->id}", ['body' => 'Changed'])
            ->assertForbidden();
    }

    public function test_message_edit_requires_a_body(): void
    {
        $seeker = $this->careSeeker();
        $message = Message::create([
            'sender_id' => $seeker->id,
            'recipient_id' => $this->careGiver()->id,
            'body' => 'Original message',
        ]);

        $this->actingAs($seeker)
            ->patchJson("/api/messages/{$message->id}", ['body' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_sender_can_delete_their_message(): void
    {
        $seeker = $this->careSeeker();
        $message = Message::create([
            'sender_id' => $seeker->id,
            'recipient_id' => $this->careGiver()->id,
            'body' => 'Delete me',
        ]);

        $this->actingAs($seeker)
            ->deleteJson("/api/messages/{$message->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('messages', ['id' => $message->id]);
    }

    public function test_recipient_cannot_delete_a_message(): void
    {
        $seeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        $message = Message::create([
            'sender_id' => $seeker->id,
            'recipient_id' => $careGiver->id,
            'body' => 'Keep me',
        ]);

        $this->actingAs($careGiver)
            ->deleteJson("/api/messages/{$message->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('messages', ['id' => $message->id]);
    }
}
