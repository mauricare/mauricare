<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case AwaitingPayment = 'awaiting_payment';
    case Paid = 'paid';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Assigned => 'Assigned',
            self::AwaitingPayment => 'Awaiting payment confirmation',
            self::Paid => 'Paid',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Closed, self::Cancelled], true);
    }

    public function seekerCanModify(): bool
    {
        return in_array($this, [self::Open, self::Assigned], true);
    }
}
