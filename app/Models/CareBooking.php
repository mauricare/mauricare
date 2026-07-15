<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'scheduled_date',
    'start_time',
    'duration_hours',
    'care_type',
    'description',
    'preferred_carer_type',
    'status',
])]
class CareBooking extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'duration_hours' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
