<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('care_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->time('start_time');
            $table->unsignedTinyInteger('duration_hours')->default(1);
            $table->string('care_type');
            $table->text('description');
            $table->string('preferred_carer_type');
            $table->string('status')->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'scheduled_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_bookings');
    }
};
