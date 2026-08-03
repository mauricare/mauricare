<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\CareBooking;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_the_admin_dashboard(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('AdminDashboard'));
    }

    public function test_non_admin_cannot_use_admin_endpoints(): void
    {
        $careSeeker = $this->careSeeker();

        $this->actingAs($careSeeker)
            ->getJson('/api/admin/care-seekers')
            ->assertForbidden();
    }

    public function test_admin_can_list_each_user_type_separately(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker(['email' => 'seeker@example.com']);
        $this->careGiver(['email' => 'giver@example.com']);

        $this->actingAs($admin)
            ->getJson('/api/admin/care-seekers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $careSeeker->id)
            ->assertJsonPath('data.0.role', 'care_seeker');

        $this->actingAs($admin)
            ->getJson('/api/admin/care-givers')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.role', 'care_giver');
    }

    public function test_admin_can_activate_and_deactivate_managed_users(): void
    {
        $admin = $this->admin();
        $careGiver = $this->careGiver();

        $this->actingAs($admin)
            ->patchJson("/api/admin/users/{$careGiver->id}/status", ['is_active' => false])
            ->assertOk()
            ->assertJsonPath('is_active', false);

        $this->assertDatabaseHas('care_giver_profiles', [
            'user_id' => $careGiver->id,
            'is_active' => false,
        ]);
    }

    public function test_care_giver_view_includes_booking_counts_and_reviews(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();
        $careGiver = $this->careGiver();
        CareBooking::factory()->for($careSeeker)->assigned($careGiver)->create();
        $closedBooking = CareBooking::factory()->for($careSeeker)->closed($careGiver)->create();
        Invoice::create([
            'invoice_number' => 'INV-PROFILE-001',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-07-01',
            'period_end' => '2026-07-31',
            'rate' => 10,
            'booking_total' => 1500,
            'amount_due' => 150,
            'paid_at' => now(),
        ]);
        Invoice::create([
            'invoice_number' => 'INV-PROFILE-002',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'rate' => 10,
            'booking_total' => 2000,
            'amount_due' => 200,
        ]);
        Review::create([
            'care_booking_id' => $closedBooking->id,
            'reviewer_id' => $careSeeker->id,
            'reviewee_id' => $careGiver->id,
            'rating' => 5,
            'comment' => 'Excellent care.',
        ]);

        $this->actingAs($admin)
            ->getJson("/api/admin/users/{$careGiver->id}")
            ->assertOk()
            ->assertJsonPath('data.booking_counts.assigned', 1)
            ->assertJsonPath('data.booking_counts.closed', 1)
            ->assertJsonPath('data.booking_counts.open', 0)
            ->assertJsonPath('data.booking_total', 2)
            ->assertJsonPath('data.invoice_count', 2)
            ->assertJsonPath('data.paid_invoice_count', 1)
            ->assertJsonPath('data.average_rating', 5)
            ->assertJsonPath('data.review_count', 1)
            ->assertJsonPath('data.reviews.0.reviewer_name', $careSeeker->name)
            ->assertJsonPath('data.reviews.0.rating', 5)
            ->assertJsonPath('data.reviews.0.comment', 'Excellent care.');
    }

    public function test_admin_can_view_and_edit_a_care_seeker_profile(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();
        CareBooking::factory()->for($careSeeker)->count(2)->create();
        CareBooking::factory()->for($careSeeker)->cancelled()->create();

        $this->actingAs($admin)
            ->getJson("/api/admin/users/{$careSeeker->id}")
            ->assertOk()
            ->assertJsonPath('data.role_profile.care_for', 'Myself')
            ->assertJsonPath('data.booking_counts.open', 2)
            ->assertJsonPath('data.booking_counts.cancelled', 1)
            ->assertJsonPath('data.booking_counts.closed', 0)
            ->assertJsonPath('data.booking_total', 3);

        $this->actingAs($admin)
            ->patchJson("/api/admin/users/{$careSeeker->id}", [
                'first_name' => 'Updated',
                'last_name' => 'Seeker',
                'email' => 'updated.seeker@example.com',
                'age' => 48,
                'phone' => '57001122',
                'address' => 'New address',
                'city' => 'Moka',
                'care_for' => 'Parent',
                'care_needs' => 'Daily assistance',
                'preferred_contact_method' => 'phone',
                'emergency_contact_name' => 'Emergency Person',
                'emergency_contact_phone' => '57002233',
                'mobility_level' => 'Limited',
                'medical_notes' => 'No allergies',
            ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Seeker');

        $this->assertDatabaseHas('users', [
            'id' => $careSeeker->id,
            'email' => 'updated.seeker@example.com',
        ]);
        $this->assertDatabaseHas('care_seeker_profiles', [
            'user_id' => $careSeeker->id,
            'care_for' => 'Parent',
        ]);
    }

    public function test_admin_can_view_and_download_a_users_private_documents(): void
    {
        Storage::fake('local');
        $admin = $this->admin();
        $careGiver = $this->careGiver();
        Storage::disk('local')->put('care-giver-cvs/cv.pdf', 'private cv');
        $document = Document::create([
            'user_id' => $careGiver->id,
            'type' => 'cv',
            'disk' => 'local',
            'path' => 'care-giver-cvs/cv.pdf',
            'original_name' => 'care-giver-cv.pdf',
            'mime_type' => 'application/pdf',
            'size' => 10,
        ]);

        $this->actingAs($admin)
            ->getJson("/api/admin/users/{$careGiver->id}")
            ->assertOk()
            ->assertJsonPath('data.documents.0.id', $document->id)
            ->assertJsonPath('data.documents.0.name', 'care-giver-cv.pdf')
            ->assertJsonPath('data.documents.0.type', 'cv')
            ->assertJsonPath('data.documents.0.download_url', route('documents.download', $document));

        $this->actingAs($admin)
            ->get(route('documents.download', $document))
            ->assertDownload('care-giver-cv.pdf');
    }

    public function test_admin_can_delete_a_managed_user_but_not_an_admin(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();
        $secondAdmin = $this->admin(['email' => 'other.admin@example.com']);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/users/{$careSeeker->id}")
            ->assertOk();
        $this->assertSoftDeleted($careSeeker);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/users/{$secondAdmin->id}")
            ->assertNotFound();
        $this->assertDatabaseHas('users', ['id' => $secondAdmin->id]);
    }

    public function test_admin_can_filter_bookings_by_status_and_cancel_an_active_booking(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();
        $open = CareBooking::factory()->for($careSeeker)->create();
        CareBooking::factory()->for($careSeeker)->cancelled()->create();
        $matchedSeeker = $this->careSeeker(['name' => 'Unique Search Person']);
        CareBooking::factory()->for($matchedSeeker)->closed()->create();

        $this->actingAs($admin)
            ->getJson('/api/admin/bookings?status=open')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $open->id)
            ->assertJsonPath('status_counts.open', 1)
            ->assertJsonPath('status_counts.cancelled', 1);

        $this->actingAs($admin)
            ->getJson('/api/admin/bookings?search=Unique%20Search')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('status_counts.open', 0)
            ->assertJsonPath('status_counts.closed', 1)
            ->assertJsonPath('status_counts.cancelled', 0);

        $this->actingAs($admin)
            ->patchJson("/api/admin/bookings/{$open->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('care_bookings', [
            'id' => $open->id,
            'status' => BookingStatus::Cancelled->value,
        ]);
    }

    public function test_admin_cannot_cancel_closed_or_cancelled_bookings(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker();
        $closed = CareBooking::factory()->for($careSeeker)->closed()->create();

        $this->actingAs($admin)
            ->patchJson("/api/admin/bookings/{$closed->id}/cancel")
            ->assertUnprocessable();

        $this->assertDatabaseHas('care_bookings', [
            'id' => $closed->id,
            'status' => BookingStatus::Closed->value,
        ]);
    }

    public function test_admin_statistics_report_monthly_booking_invoice_and_join_metrics(): void
    {
        $admin = $this->admin();
        $careSeeker = $this->careSeeker(['created_at' => '2026-08-02 09:00:00']);
        $careGiver = $this->careGiver(['created_at' => '2026-08-02 10:00:00']);
        CareBooking::factory()->for($careSeeker)->closed($careGiver)->create([
            'created_at' => '2026-08-03 09:00:00',
        ]);
        CareBooking::factory()->for($careSeeker)->cancelled()->create([
            'created_at' => '2026-08-04 09:00:00',
        ]);
        Invoice::create([
            'invoice_number' => 'INV-STATS-PAID',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'rate' => 10,
            'booking_total' => 2500,
            'amount_due' => 250,
            'paid_at' => '2026-08-05 09:00:00',
            'created_at' => '2026-08-04 09:00:00',
        ]);
        Invoice::create([
            'invoice_number' => 'INV-STATS-UNPAID',
            'care_giver_id' => $careGiver->id,
            'generated_by' => $admin->id,
            'period_start' => '2026-08-01',
            'period_end' => '2026-08-31',
            'rate' => 10,
            'booking_total' => 1000,
            'amount_due' => 100,
            'created_at' => '2026-08-06 09:00:00',
        ]);

        $this->actingAs($admin)
            ->getJson('/api/admin/statistics?year=2026&month=8')
            ->assertOk()
            ->assertJsonPath('data.paid_invoice_total', '250.00')
            ->assertJsonPath('data.paid_invoice_count', 1)
            ->assertJsonPath('data.invoices_generated', 2)
            ->assertJsonPath('data.unpaid_invoice_count', 1)
            ->assertJsonPath('data.unpaid_invoice_total', '100.00')
            ->assertJsonPath('data.bookings_created', 2)
            ->assertJsonPath('data.bookings_closed', 1)
            ->assertJsonPath('data.bookings_cancelled', 1)
            ->assertJsonPath('data.booking_closure_rate', 50)
            ->assertJsonPath('data.care_seekers_joined', 1)
            ->assertJsonPath('data.care_givers_joined', 1)
            ->assertJsonCount(12, 'monthly');
    }

    private function admin(array $attributes = []): User
    {
        Role::findOrCreate('admin');
        $user = User::factory()->create($attributes);
        $user->assignRole('admin');

        return $user;
    }

    private function careSeeker(array $attributes = []): User
    {
        Role::findOrCreate('care_seeker');
        $user = User::factory()->create($attributes);
        $user->assignRole('care_seeker');
        $user->profile()->create([
            'first_name' => 'Care',
            'last_name' => 'Seeker',
            'age' => 40,
            'phone' => '57000000',
            'address' => 'Test address',
            'city' => 'Port Louis',
        ]);
        $user->careSeekerProfile()->create([
            'care_for' => 'Myself',
            'care_needs' => 'Daily care',
            'is_active' => true,
        ]);

        return $user;
    }

    private function careGiver(array $attributes = []): User
    {
        Role::findOrCreate('care_giver');
        $user = User::factory()->create($attributes);
        $user->assignRole('care_giver');
        $user->profile()->create([
            'first_name' => 'Care',
            'last_name' => 'Giver',
            'age' => 35,
            'phone' => '57000001',
            'address' => 'Test address',
            'city' => 'Curepipe',
        ]);
        $user->careGiverProfile()->create([
            'type' => 'nurse',
            'is_active' => true,
        ]);

        return $user;
    }
}
