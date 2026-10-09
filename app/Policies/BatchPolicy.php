<?php

namespace App\Policies;

use App\Models\Batch;
use App\Models\User;

class BatchPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Batch $batch): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    /**
     * A recalled batch is frozen: its data is evidence for the recall.
     */
    public function update(User $user, Batch $batch): bool
    {
        return ! $batch->isRecalled();
    }

    public function recall(User $user, Batch $batch): bool
    {
        return $user->isAdmin() && ! $batch->isRecalled();
    }

    /**
     * Any user may record stock leaving the chain (sales, damage...).
     * Every removal is attributed in the ledger and the audit trail.
     */
    public function removeStock(User $user, Batch $batch): bool
    {
        return ! $batch->isEmpty() && $batch->origin_location_id !== null;
    }

    /**
     * Batches registered before stock tracking need a starting location.
     */
    public function assignOpeningStock(User $user, Batch $batch): bool
    {
        return $user->isAdmin() && $batch->origin_location_id === null;
    }
}
