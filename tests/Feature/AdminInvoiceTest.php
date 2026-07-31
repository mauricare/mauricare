<?php

namespace Tests\Feature;

use App\Mail\CareGiverInvoice;
use App\Models\CareBooking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    public function test_admin_generates_an_invoice_from_only_eligible_closed_bookings(): void
    {
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        $seeker = $this->userWithRole('care_seeker');

        $first = CareBooking::factory()->for($seeker)->closed($careGiver)->create([
            'scheduled_date' => '2026-07-05',
            'amount_paid' => 1000,
        ]);
        $second = CareBooking::factory()->for($seeker)->closed($careGiver)->create([
            'scheduled_date' => '2026-07-20',
            'amount_paid' => 2500,
        ]);
        CareBooking::factory()->for($seeker)->closed($careGiver)->create([
            'scheduled_date' => '2026-06-30',
            'amount_paid' => 9000,
        ]);
        CareBooking::factory()->for($seeker)->assigned($careGiver)->create([
            'scheduled_date' => '2026-07-10',
        ]);

        $response = $this->actingAs($admin)->postJson('/api/admin/invoices', [
            'care_giver_id' => $careGiver->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.booking_total', '3500.00')
            ->assertJsonPath('data.amount_due', '350.00')
            ->assertJsonPath('data.bookings_count', 2)
            ->assertJsonCount(2, 'data.bookings');

        $invoiceId = $response->json('data.id');
        $this->assertSame($invoiceId, $first->fresh()->invoice_id);
        $this->assertSame($invoiceId, $second->fresh()->invoice_id);
    }

    public function test_invoiced_bookings_are_not_included_again(): void
    {
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        CareBooking::factory()->closed($careGiver)->create([
            'scheduled_date' => '2026-07-15',
            'amount_paid' => 1500,
        ]);
        $payload = [
            'care_giver_id' => $careGiver->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
        ];

        $this->actingAs($admin)->postJson('/api/admin/invoices', $payload)->assertCreated();
        $this->actingAs($admin)->postJson('/api/admin/invoices', $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('period_start');

        $this->assertDatabaseCount('invoices', 1);
    }

    public function test_admin_can_estimate_eligible_bookings_before_generating(): void
    {
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        CareBooking::factory()->closed($careGiver)->create([
            'scheduled_date' => '2026-07-10',
            'amount_paid' => 1200,
        ]);
        CareBooking::factory()->closed($careGiver)->create([
            'scheduled_date' => '2026-07-20',
            'amount_paid' => 1800,
        ]);
        CareBooking::factory()->closed($careGiver)->create([
            'scheduled_date' => '2026-08-01',
            'amount_paid' => 9000,
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/invoices/estimate?care_giver_id='.$careGiver->id.'&period_start=2026-07-01&period_end=2026-07-31')
            ->assertOk()
            ->assertJsonPath('data.bookings_count', 2)
            ->assertJsonPath('data.booking_total', '3000.00');
    }

    public function test_invoice_endpoints_are_restricted_to_admins(): void
    {
        $careGiver = $this->userWithRole('care_giver');

        $this->actingAs($careGiver)->getJson('/api/admin/invoices')->assertForbidden();
        $this->actingAs($careGiver)->postJson('/api/admin/invoices', [])->assertForbidden();
    }

    public function test_admin_can_reopen_an_invoice_preview(): void
    {
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        $invoice = Invoice::create([
            'invoice_number' => 'INV-TEST-001',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
            'booking_total' => 1500,
            'amount_due' => 150,
        ]);
        CareBooking::factory()->closed($careGiver)->create([
            'invoice_id' => $invoice->id,
            'scheduled_date' => '2026-07-15',
        ]);

        $this->actingAs($admin)
            ->getJson("/api/admin/invoices/{$invoice->id}")
            ->assertOk()
            ->assertJsonPath('data.invoice_number', 'INV-TEST-001')
            ->assertJsonCount(1, 'data.bookings');
    }

    public function test_deleted_invoices_are_not_listed_or_available_for_preview(): void
    {
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        $invoice = Invoice::create([
            'invoice_number' => 'INV-DELETED-001',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
            'booking_total' => 1500,
            'amount_due' => 150,
        ]);
        DB::table('invoices')->where('id', $invoice->id)->update(['deleted_at' => now()]);

        $this->actingAs($admin)
            ->getJson('/api/admin/invoices')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->actingAs($admin)
            ->getJson("/api/admin/invoices/{$invoice->id}")
            ->assertNotFound();
    }

    public function test_admin_can_email_an_invoice_pdf_to_the_care_giver(): void
    {
        Mail::fake();
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        $invoice = Invoice::create([
            'invoice_number' => 'INV-EMAIL-001',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
            'booking_total' => 1500,
            'amount_due' => 150,
        ]);
        CareBooking::factory()->closed($careGiver)->create([
            'invoice_id' => $invoice->id,
            'scheduled_date' => '2026-07-15',
        ]);
        $recipient = 'accounts@example.com';

        $this->actingAs($admin)
            ->postJson("/api/admin/invoices/{$invoice->id}/send", ['email' => $recipient])
            ->assertOk()
            ->assertJsonPath('message', "Invoice sent to {$recipient}.")
            ->assertJsonPath('data.sent_count', 1);

        Mail::assertSent(
            CareGiverInvoice::class,
            fn (CareGiverInvoice $mail): bool => $mail->hasTo($recipient)
                && $mail->invoice->is($invoice)
                && count($mail->attachments()) === 1,
        );

        $this->assertNotNull($invoice->fresh()->sent_at);

        $this->actingAs($admin)
            ->postJson("/api/admin/invoices/{$invoice->id}/send", ['email' => $careGiver->email])
            ->assertOk()
            ->assertJsonPath('data.sent_count', 2);

        Mail::assertSent(CareGiverInvoice::class, 2);
    }

    public function test_invoice_email_recipient_must_be_valid(): void
    {
        Mail::fake();
        $admin = $this->userWithRole('admin');
        $careGiver = $this->userWithRole('care_giver');
        $invoice = Invoice::create([
            'invoice_number' => 'INV-EMAIL-VALIDATION',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
            'booking_total' => 1500,
            'amount_due' => 150,
        ]);

        $this->actingAs($admin)
            ->postJson("/api/admin/invoices/{$invoice->id}/send", ['email' => 'not-an-email'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('email');

        Mail::assertNothingSent();
    }
}
