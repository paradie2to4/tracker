<?php

namespace App\Actions\Batches;

use App\Models\Batch;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Support\Facades\DB;

/**
 * Records a recall decision. Irreversible in the current version.
 */
final class RecallBatch
{
    /**
     * @return bool False if the batch had already been recalled.
     */
    public function handle(Batch $batch, string $reason, User $actor): bool
    {
        return DB::transaction(function () use ($batch, $reason, $actor) {
            // Conditional UPDATE ... WHERE recalled_at IS NULL: if two admins
            // recall the same batch at the same moment, exactly one succeeds
            // and the original recall details are never overwritten.
            $updated = Batch::whereKey($batch->getKey())
                ->whereNull('recalled_at')
                ->update([
                    'recalled_at' => now(),
                    'recall_reason' => $reason,
                    'recalled_by' => $actor->getKey(),
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return false;
            }

            // A query-builder update fires no model events, so the recall is
            // audited explicitly.
            AuditLogger::record('batch.recalled', $batch, null, ['recall_reason' => $reason], $actor);

            return true;
        });
    }
}
