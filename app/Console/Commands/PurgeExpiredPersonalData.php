<?php

namespace App\Console\Commands;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PurgeExpiredPersonalData extends Command
{
    protected $signature = 'privacy:purge-expired {--dry-run}';

    protected $description = 'Erase or anonymise personal data whose documented retention period has expired';

    public function handle(): int
    {
        $cutoff = now()->subDays(config('privacy.account_retention_days'));
        $users = User::onlyTrashed()->where('erasure_requested_at', '<=', $cutoff)->withTrashed()->get();

        if ($this->option('dry-run')) {
            $this->info("{$users->count()} account(s) eligible for erasure.");

            return self::SUCCESS;
        }

        foreach ($users as $user) {
            DB::transaction(function () use ($user): void {
                foreach ($user->documents()->withTrashed()->get() as $document) {
                    Storage::disk($document->disk)->delete($document->path);
                    $document->forceDelete();
                }
                $user->clearMediaCollection('avatar');
                $user->careSeekerProfile()->withTrashed()->update([
                    'care_for' => null, 'care_needs' => null, 'preferred_contact_method' => null,
                    'emergency_contact_name' => null, 'emergency_contact_phone' => null,
                    'mobility_level' => null, 'medical_notes' => null,
                ]);
                $user->profile()->withTrashed()->update([
                    'first_name' => 'Deleted', 'last_name' => 'User', 'age' => null,
                    'phone' => null, 'address' => null, 'city' => null,
                ]);
                DB::table('messages')->where('sender_id', $user->id)->orWhere('recipient_id', $user->id)
                    ->update(['body' => '[removed]', 'updated_at' => now()]);
                $user->forceFill([
                    'name' => 'Deleted User',
                    'email' => "deleted-{$user->id}-".Str::lower(Str::random(12)).'@invalid.mauricare',
                    'password' => Hash::make(Str::random(64)),
                    'remember_token' => null,
                ])->saveQuietly();
            });
        }

        AuditEvent::where('created_at', '<', now()->subDays(config('privacy.audit_retention_days')))->delete();
        $this->info("Erased {$users->count()} expired account(s).");

        return self::SUCCESS;
    }
}
