<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('care_seeker_profiles', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('medical_notes');
        });

        DB::table('care_seeker_profiles')->update(['is_active' => true]);
    }

    public function down(): void
    {
        Schema::table('care_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
