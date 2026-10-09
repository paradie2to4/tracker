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
            self::InTransit => 'bg-honey-50 text-honey-700 ring-honey-600/20',
            self::Received => 'bg-brand-50 text-brand-700 ring-brand-600/20',
            self::Cancelled => 'bg-ink-100 text-ink-600 ring-ink-500/20',
        };
    }
}
