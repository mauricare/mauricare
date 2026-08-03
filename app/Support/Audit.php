<?php

namespace App\Support;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Audit
{
    public static function record(Request $request, string $event, ?User $subject = null, ?Model $auditable = null, array $metadata = []): void
    {
        AuditEvent::create([
            'actor_id' => $request->session()->get('impersonator_id') ?? $request->user()?->getKey(),
            'subject_user_id' => $subject?->getKey(),
            'event' => $event,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata ?: null,
        ]);
    }
}
