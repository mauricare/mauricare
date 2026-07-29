<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'care_giver_id',
    'scheduled_date',
    'start_time',
    'duration_hours',
    'care_type',
    'description',
    'preferred_carer_type',
    'address',
    'contact_phone',
    'status',
    'amount_due',
    'amount_paid',
    'payment_method',
    'payment_reference',
])]
class CareBooking extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date:Y-m-d',
            'duration_hours' => 'integer',
            'status' => BookingStatus::class,
            'amount_due' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'payment_method' => PaymentMethod::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function careGiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'care_giver_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }
}
