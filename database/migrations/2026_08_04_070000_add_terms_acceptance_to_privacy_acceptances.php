<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('privacy_acceptances', function (Blueprint $table): void {
            $table->string('terms_version', 40)->nullable()->after('notice_accepted_at');
            $table->timestamp('terms_accepted_at')->nullable()->after('terms_version');
            $table->index(['user_id', 'terms_version']);
        });
    }

    public function down(): void
    {
        Schema::table('privacy_acceptances', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'terms_version']);
            $table->dropColumn(['terms_version', 'terms_accepted_at']);
        });
    }
};
