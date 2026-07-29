<?php

namespace Tests\Feature;

use App\Mail\ContactEnquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        return [
            'name' => 'Test Person',
            'phone' => '58199909',
            'email' => 'person@example.com',
            'message' => 'Please contact me about home care.',
        ];
    }

    public function test_public_contact_form_sends_to_info_address(): void
    {
        Mail::fake();

        $this->from('/')->post('/contact', $this->payload())
            ->assertRedirect('/');

        Mail::assertSent(ContactEnquiry::class, fn (ContactEnquiry $mail): bool => $mail
            ->hasTo('info@mauricare.mu')
            && $mail->mailSubject === 'New Mauricare contact enquiry');
    }

    public function test_dashboard_support_form_sends_to_contact_address(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from('/dashboard?section=help')
            ->post('/support/contact', $this->payload())
            ->assertRedirect('/dashboard?section=help');

        Mail::assertSent(ContactEnquiry::class, fn (ContactEnquiry $mail): bool => $mail
            ->hasTo('contact@mauricare.mu')
            && $mail->mailSubject === 'New Mauricare support request');
    }

    public function test_guest_cannot_submit_dashboard_support_form(): void
    {
        Mail::fake();

        $this->post('/support/contact', $this->payload())
            ->assertRedirect('/login');

        Mail::assertNothingSent();
    }
}
