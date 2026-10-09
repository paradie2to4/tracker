<?php

namespace App\Enums;

/**
 * Allowed transitions:
 *
 *   in_transit -> received   (stock is credited to the destination)
 *   in_transit -> cancelled  (stock is returned to the origin)
 *
 * received and cancelled are terminal.
 */
enum ShipmentStatus: string
{
    case InTransit = 'in_transit';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::InTransit => 'In transit',
            self::Received => 'Received',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::InTransit => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            self::Received => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Cancelled => 'bg-ink-100 text-ink-600 ring-ink-500/20',
        };
    }
}
