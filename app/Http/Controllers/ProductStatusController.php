<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Activates or deactivates a product (administrators only).
 *
 * Deactivation is the safe alternative to deletion: the product and its
 * batch history remain, but no new batches can be registered for it.
 */
class ProductStatusController extends Controller
{
    public function __invoke(Request $request, Product $product): RedirectResponse
    {
        Gate::authorize('changeStatus', $product);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $product->is_active = (bool) $validated['is_active'];
        $product->save();

        return redirect()
            ->route('products.show', $product)
            ->with('success', $product->is_active
                ? 'The product has been activated.'
                : 'The product has been deactivated. No new batches can be registered for it.');
    }
}
