<?php

namespace App\Enums;

/**
 * Each stock movement changes the balance of exactly one location:
 *
 *   production          +qty at to_location    (batch registered)
 *   dispatch            -qty at from_location  (stock leaves on a shipment)
 *   receipt             +qty at to_location    (shipment received)
 *   cancellation_return +qty at to_location    (cancelled shipment, back to origin)
 *   removal             -qty at from_location  (sold, consumed, damaged, ...)
 *
 * Only production and removal change the batch's total current_quantity;
 * dispatch/receipt move stock between locations via "in transit".
 */
enum MovementType: string
{
    case Production = 'production';
    case Dispatch = 'dispatch';
    case Receipt = 'receipt';
    case CancellationReturn = 'cancellation_return';
    case Removal = 'removal';

    public function label(): string
    {
        return match ($this) {
            self::Production => 'Produced',
            self::Dispatch => 'Dispatched',
            self::Receipt => 'Received',
            self::CancellationReturn => 'Returned (shipment cancelled)',
            self::Removal => 'Removed from stock',
        };
    }

    /**
     * +1 when the movement adds stock to its location, -1 when it removes it.
     */
    public function direction(): int
    {
        return match ($this) {
            self::Production, self::Receipt, self::CancellationReturn => 1,
            self::Dispatch, self::Removal => -1,
        };
    }
}
