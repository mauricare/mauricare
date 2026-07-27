<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Juice = 'juice';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Juice => 'Juice',
        };
    }

    public function requiresReference(): bool
    {
        return $this === self::Juice;
    }
}
