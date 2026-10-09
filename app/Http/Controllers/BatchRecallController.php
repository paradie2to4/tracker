<?php

namespace App\Http\Controllers;

use App\Http\Requests\RecallBatchRequest;
use App\Models\Batch;
use Illuminate\Http\RedirectResponse;

/**
 * Records a recall decision for a batch (administrators only).
 *
 * A recall is irreversible in the MVP. Full recall workflows (affected
 * shipments, notifications, reports) are planned for Phase 7.
 */
class BatchRecallController extends Controller
{
    public function __invoke(RecallBatchRequest $request, Batch $batch): RedirectResponse
    {
        // Conditional UPDATE ... WHERE recalled_at IS NULL: if two admins
        // recall the same batch at the same moment, exactly one succeeds and
        // the original recall details are never overwritten.
        $updated = Batch::whereKey($batch->getKey())
            ->whereNull('recalled_at')
            ->update([
                'recalled_at' => now(),
                'recall_reason' => $request->validated('recall_reason'),
                'recalled_by' => $request->user()->getKey(),
                'updated_at' => now(),
            ]);

        if ($updated === 0) {
            return redirect()
                ->route('batches.show', $batch)
                ->with('error', 'This batch has already been recalled.');
        }

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', "Batch {$batch->batch_number} has been recalled.");
    }
}
