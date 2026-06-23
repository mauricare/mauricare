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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('password');
            $table->string('last_name')->nullable()->after('first_name');
            $table->unsignedTinyInteger('age')->nullable()->after('last_name');
            $table->string('phone')->nullable()->after('age');
            $table->string('address')->nullable()->after('phone');
            $table->string('city')->nullable()->after('address');

            $table->enum('care_giver_type', ['doctor', 'nurse', 'carers', 'physiotherapist', 'other'])
                ->nullable()
                ->after('city');
            $table->string('cv_path')->nullable()->after('care_giver_type');

            $table->string('care_for')->nullable()->after('cv_path');
            $table->text('care_needs')->nullable()->after('care_for');
            $table->string('preferred_contact_method')->nullable()->after('care_needs');
            $table->string('emergency_contact_name')->nullable()->after('preferred_contact_method');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            $table->string('mobility_level')->nullable()->after('emergency_contact_phone');
            $table->text('medical_notes')->nullable()->after('mobility_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'age',
                'phone',
                'address',
                'city',
                'care_giver_type',
                'cv_path',
                'care_for',
                'care_needs',
                'preferred_contact_method',
                'emergency_contact_name',
                'emergency_contact_phone',
                'mobility_level',
                'medical_notes',
            ]);
        });
    }
};
