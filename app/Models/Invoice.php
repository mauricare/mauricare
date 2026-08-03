<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'invoice_number',
    'care_giver_id',
    'generated_by',
    'period_start',
    'period_end',
    'rate',
    'booking_total',
    'amount_due',
    'sent_at',
    'sent_count',
    'paid_at',
])]
class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'period_start' => 'date:Y-m-d',
            'period_end' => 'date:Y-m-d',
            'rate' => 'decimal:2',
            'booking_total' => 'decimal:2',
            'amount_due' => 'decimal:2',
            'sent_at' => 'datetime',
            'sent_count' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function careGiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'care_giver_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(CareBooking::class);
    }
}
