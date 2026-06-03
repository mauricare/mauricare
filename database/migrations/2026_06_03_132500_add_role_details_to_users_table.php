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
            $table->string('role')->default('patient')->after('password');
            $table->string('phone')->nullable()->after('role');
            $table->string('address')->nullable()->after('phone');
            $table->string('care_for')->nullable()->after('address');
            $table->text('care_needs')->nullable()->after('care_for');
            $table->unsignedTinyInteger('experience_years')->nullable()->after('care_needs');
            $table->string('qualification')->nullable()->after('experience_years');
            $table->string('availability')->nullable()->after('qualification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'address',
                'care_for',
                'care_needs',
                'experience_years',
                'qualification',
                'availability',
            ]);
        });
    }
};
