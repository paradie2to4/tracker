<?php

namespace App\Http\Controllers;

use App\Actions\Batches\RecallBatch;
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
    public function __invoke(RecallBatchRequest $request, Batch $batch, RecallBatch $recall): RedirectResponse
    {
        if (! $recall->handle($batch, $request->validated('recall_reason'), $request->user())) {
            return redirect()
                ->route('batches.show', $batch)
                ->with('error', 'This batch has already been recalled.');
        }

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', "Batch {$batch->batch_number} has been recalled.");
    }
}
