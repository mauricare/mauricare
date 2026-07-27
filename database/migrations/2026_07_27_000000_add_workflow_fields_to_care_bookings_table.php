<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('care_bookings', function (Blueprint $table) {
            $table->foreignId('care_giver_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('address')->nullable()->after('preferred_carer_type');
            $table->string('contact_phone')->nullable()->after('address');
            $table->decimal('amount_due', 10, 2)->nullable()->after('status');
            $table->decimal('amount_paid', 10, 2)->nullable()->after('amount_due');
            $table->string('payment_method')->nullable()->after('amount_paid');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->string('status')->default('open')->change();
        });

        DB::table('care_bookings')->where('status', 'pending')->update(['status' => 'open']);
        DB::table('care_bookings')->where('status', 'confirmed')->update(['status' => 'assigned']);
        DB::table('care_bookings')->where('status', 'completed')->update(['status' => 'closed']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('care_bookings')->where('status', 'open')->update(['status' => 'pending']);
        DB::table('care_bookings')->where('status', 'assigned')->update(['status' => 'confirmed']);
        DB::table('care_bookings')->whereIn('status', ['awaiting_payment', 'paid', 'closed'])->update(['status' => 'completed']);

        Schema::table('care_bookings', function (Blueprint $table) {
            $table->dropConstrainedForeignId('care_giver_id');
            $table->dropColumn([
                'address',
                'contact_phone',
                'amount_due',
                'amount_paid',
                'payment_method',
                'payment_reference',
            ]);
            $table->string('status')->default('pending')->change();
        });
    }
};
