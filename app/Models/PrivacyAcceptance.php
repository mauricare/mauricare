<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'notice_version', 'notice_accepted_at', 'health_data_consent_at', 'data_subject_authority_confirmed_at', 'ip_address', 'user_agent'])]
class PrivacyAcceptance extends Model
{
    protected function casts(): array
    {
        return [
            'notice_accepted_at' => 'datetime',
            'health_data_consent_at' => 'datetime',
            'data_subject_authority_confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
