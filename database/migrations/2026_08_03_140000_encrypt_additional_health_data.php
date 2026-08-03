<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $fields = ['care_needs', 'emergency_contact_name', 'emergency_contact_phone', 'mobility_level'];

    public function up(): void
    {
        Schema::table('care_seeker_profiles', function (Blueprint $table): void {
            foreach ($this->fields as $field) {
                $table->text($field)->nullable()->change();
            }
        });

        DB::table('care_seeker_profiles')->orderBy('id')->eachById(function (object $profile): void {
            $updates = [];
            foreach ($this->fields as $field) {
                if (! isset($profile->{$field}) || $profile->{$field} === '') {
                    continue;
                }
                try {
                    Crypt::decryptString($profile->{$field});
                } catch (DecryptException) {
                    $updates[$field] = Crypt::encryptString($profile->{$field});
                }
            }
            if ($updates) {
                DB::table('care_seeker_profiles')->where('id', $profile->id)->update($updates);
            }
        });
    }

    public function down(): void
    {
        DB::table('care_seeker_profiles')->orderBy('id')->eachById(function (object $profile): void {
            $updates = [];
            foreach ($this->fields as $field) {
                if (! isset($profile->{$field}) || $profile->{$field} === '') {
                    continue;
                }
                try {
                    $updates[$field] = Crypt::decryptString($profile->{$field});
                } catch (DecryptException) {
                    // Already plaintext.
                }
            }
            if ($updates) {
                DB::table('care_seeker_profiles')->where('id', $profile->id)->update($updates);
            }
        });
    }
};
