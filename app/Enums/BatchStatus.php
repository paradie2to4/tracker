<?php

namespace App\Enums;

/**
 * The effective status of a batch.
 *
 * Status is never stored as a column. It is derived from facts that are
 * stored, so it can never go stale:
 *
 *   recalled  -> recalled_at is set (an explicit, irreversible decision)
 *   depleted  -> current_quantity is zero
 *   expired   -> expiry_date is before today
 *   active    -> none of the above
 *
 * Precedence is top to bottom. A recall always wins because it is the most
 * safety-critical fact. Depleted beats expired because an empty batch has
 * no stock left that could be sold after its expiry date.
 */
enum BatchStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case Recalled = 'recalled';
    case Depleted = 'depleted';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Tailwind classes for the status badge.
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::Active => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            self::Expired => 'bg-amber-50 text-amber-800 ring-amber-600/20',
            self::Recalled => 'bg-red-50 text-red-700 ring-red-600/20',
            self::Depleted => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }
}
