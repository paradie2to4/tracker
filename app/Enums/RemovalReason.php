<?php

namespace App\Enums;

enum RemovalReason: string
{
    case Sold = 'sold';
    case Consumed = 'consumed';
    case Damaged = 'damaged';
    case Disposed = 'disposed';
    case Lost = 'lost';
    case Correction = 'correction';

    public function label(): string
    {
        return match ($this) {
            self::Sold => 'Sold to end customers',
            self::Consumed => 'Used / consumed',
            self::Damaged => 'Damaged',
            self::Disposed => 'Disposed of (expired or recalled)',
            self::Lost => 'Lost or stolen',
            self::Correction => 'Stock count correction',
        };
    }
}
