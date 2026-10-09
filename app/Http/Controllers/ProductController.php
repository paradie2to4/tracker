<?php

namespace App\Http\Controllers;

use App\Enums\ProductCategory;
use App\Enums\UnitOfMeasure;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Support\PageSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * There is intentionally no destroy() action: products are deactivated
 * instead of deleted (see ProductStatusController), and the database
 * refuses to delete a product that still has batches.
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);

        // Unknown filter values are ignored rather than rejected, so a
        // stale bookmark never produces an error page.
        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'category' => ProductCategory::tryFrom((string) $request->query('category')),
            'status' => in_array($request->query('status'), ['active', 'inactive'], true) ? $request->query('status') : null,
        ];

        $products = Product::query()
            ->withCount('batches')
            ->search($filters['q'])
            ->when($filters['category'], fn ($query, ProductCategory $category) => $query->where('category', $category))
            ->when($filters['status'], fn ($query, string $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->paginate(PageSize::for($request))
            ->withQueryString();

        return view('products.index', [
            'products' => $products,
            'filters' => $filters,
            'categories' => ProductCategory::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('products.create', [
            'product' => new Product,
            'categories' => ProductCategory::cases(),
            'units' => UnitOfMeasure::cases(),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('success', "Product {$product->product_code} has been registered.");
    }

    public function show(Product $product): View
    {
        Gate::authorize('view', $product);

        return view('products.show', [
            'product' => $product,
            'batches' => $product->batches()
                ->orderByDesc('manufacturing_date')
                ->orderByDesc('id')
                ->paginate(PageSize::for(request(), PageSize::NESTED)),
        ]);
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        return view('products.edit', [
            'product' => $product,
            'categories' => ProductCategory::cases(),
            'units' => UnitOfMeasure::cases(),
            'codeLocked' => $product->batches()->exists(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product details have been updated.');
    }
}
