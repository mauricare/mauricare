<?php

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('care_seeker_profiles')
            ->whereNotNull('medical_notes')
            ->where('medical_notes', '!=', '')
            ->orderBy('id')
            ->eachById(function (object $profile): void {
                try {
                    Crypt::decryptString($profile->medical_notes);

                    return;
                } catch (DecryptException) {
                    // This is an existing plaintext value that still needs encryption.
                }

                DB::table('care_seeker_profiles')
                    ->where('id', $profile->id)
                    ->update(['medical_notes' => Crypt::encryptString($profile->medical_notes)]);
            });

        DB::table('documents')
            ->where('disk', 'public')
            ->whereIn('type', ['cv', 'agency_license'])
            ->orderBy('id')
            ->eachById(function (object $document): void {
                if (! Storage::disk('public')->exists($document->path)) {
                    DB::table('documents')->where('id', $document->id)->update(['disk' => 'local']);

                    return;
                }

                $contents = Storage::disk('public')->readStream($document->path);

                if ($contents === null || ! Storage::disk('local')->writeStream($document->path, $contents)) {
                    if (is_resource($contents)) {
                        fclose($contents);
                    }

                    throw new RuntimeException("Document {$document->id} could not be copied to private storage.");
                }

                if (is_resource($contents)) {
                    fclose($contents);
                }

                if (! Storage::disk('public')->delete($document->path)) {
                    throw new RuntimeException("Document {$document->id} could not be removed from public storage.");
                }

                DB::table('documents')->where('id', $document->id)->update(['disk' => 'local']);
            });
    }

    public function down(): void
    {
        DB::table('care_seeker_profiles')
            ->whereNotNull('medical_notes')
            ->where('medical_notes', '!=', '')
            ->orderBy('id')
            ->eachById(function (object $profile): void {
                try {
                    $medicalNotes = Crypt::decryptString($profile->medical_notes);
                } catch (DecryptException) {
                    return;
                }

                DB::table('care_seeker_profiles')
                    ->where('id', $profile->id)
                    ->update(['medical_notes' => $medicalNotes]);
            });
    }
};
