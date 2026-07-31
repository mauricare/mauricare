<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('care_giver_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('generated_by')->constrained('users')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('rate', 5, 2);
            $table->decimal('booking_total', 12, 2);
            $table->decimal('amount_due', 12, 2);
            $table->timestamps();
        });

        Schema::table('care_bookings', function (Blueprint $table) {
            $table->foreignId('invoice_id')
                ->nullable()
                ->after('payment_reference')
                ->constrained()
                ->restrictOnDelete();
            $table->index(['care_giver_id', 'status', 'scheduled_date', 'invoice_id'], 'bookings_invoice_eligibility_index');
        });
    }

    public function down(): void
    {
        Schema::table('care_bookings', function (Blueprint $table) {
            $table->dropIndex('bookings_invoice_eligibility_index');
            $table->dropConstrainedForeignId('invoice_id');
        });

        Schema::dropIfExists('invoices');
    }
};
