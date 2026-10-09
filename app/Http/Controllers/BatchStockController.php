<?php

namespace App\Http\Controllers;

use App\Actions\Batches\AssignOpeningStock;
use App\Actions\Stock\RecordStockRemoval;
use App\Enums\RemovalReason;
use App\Http\Requests\RecordStockRemovalRequest;
use App\Models\Batch;
use App\Models\Location;
use App\Support\Quantity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Stock operations on a single batch, triggered from the batch page.
 */
class BatchStockController extends Controller
{
    public function remove(RecordStockRemovalRequest $request, Batch $batch, RecordStockRemoval $removal): RedirectResponse
    {
        $removal->handle(
            $batch,
            Location::findOrFail($request->integer('location_id')),
            $request->validated('quantity'),
            RemovalReason::from($request->validated('reason')),
            $request->validated('notes'),
            $request->user(),
        );

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', 'Removed '.Quantity::format($request->validated('quantity')).' from stock.');
    }

    public function assignOpening(Request $request, Batch $batch, AssignOpeningStock $assign): RedirectResponse
    {
        Gate::authorize('assignOpeningStock', $batch);

        $validated = $request->validate([
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
        ]);

        $assign->handle($batch, Location::findOrFail($validated['location_id']), $request->user());

        return redirect()
            ->route('batches.show', $batch)
            ->with('success', 'Opening stock has been assigned. This batch can now be shipped and tracked.');
    }
}
