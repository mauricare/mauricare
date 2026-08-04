<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('care_options', function (Blueprint $table): void {
            $table->id();
            $table->string('category', 30);
            $table->string('value', 100);
            $table->string('label');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['category', 'value']);
            $table->index(['category', 'is_active', 'sort_order']);
        });

        $now = now();
        $rows = [
            ['care_type', 'personal_care', 'Personal Care Assistance'],
            ['care_type', 'nursing_care', 'Nursing Care'],
            ['care_type', 'physiotherapy', 'Physiotherapy Session'],
            ['care_type', 'post_hospital_recovery', 'Post-Hospital Recovery'],
            ['care_type', 'respite_care', 'Respite Care'],
            ['care_type', 'companionship', 'Companionship'],
            ['care_type', 'wound_care', 'Wound Care'],
            ['care_type', 'home_icu_support', 'Home ICU Support'],
            ['care_type', 'other', 'Other Care'],
            ['carer_type', 'doctor', 'Doctor'],
            ['carer_type', 'nurse', 'Nurse'],
            ['carer_type', 'carers', 'Carer'],
            ['carer_type', 'physiotherapist', 'Physiotherapist'],
            ['carer_type', 'other', 'Other'],
        ];

        DB::table('care_options')->insert(collect($rows)->map(fn (array $row, int $index): array => [
            'category' => $row[0],
            'value' => $row[1],
            'label' => $row[2],
            'sort_order' => $index,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('care_options');
    }
};
