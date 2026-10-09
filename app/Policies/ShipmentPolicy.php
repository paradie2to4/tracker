<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;

/**
 * The status checks here hide buttons and reject requests early. The
 * actions repeat them under a row lock, which is what actually guarantees
 * a shipment is received or cancelled at most once.
 */
class ShipmentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function receive(User $user, Shipment $shipment): bool
    {
        return $shipment->isInTransit();
    }

    /**
     * Only the person who dispatched the shipment, or an administrator.
     */
    public function cancel(User $user, Shipment $shipment): bool
    {
        return $shipment->isInTransit()
            && ($user->isAdmin() || $shipment->dispatched_by === $user->getKey());
    }
}
