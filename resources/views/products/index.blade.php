<x-layouts.app title="Products">
    <x-slot:actions>
        @can('create', App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="btn btn-primary">Register product</a>
        @endcan
    </x-slot:actions>

    <form method="GET" action="{{ route('products.index') }}" role="search" class="card mb-6 grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-[2fr_1fr_1fr_auto] lg:items-end">
        <div>
            <label for="q" class="block text-sm font-medium text-ink-700">Search</label>
            <input type="search" id="q" name="q" value="{{ $filters['q'] }}" placeholder="Product name or code" maxlength="100" class="form-control mt-1.5">
        </div>
        <div>
            <label for="category" class="block text-sm font-medium text-ink-700">Category</label>
            <select id="category" name="category" class="form-control mt-1.5">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->value }}" @selected($filters['category'] === $category)>{{ $category->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-ink-700">Status</label>
            <select id="status" name="status" class="form-control mt-1.5">
                <option value="">All statuses</option>
                <option value="active" @selected($filters['status'] === 'active')>Active</option>
                <option value="inactive" @selected($filters['status'] === 'inactive')>Inactive</option>
            </select>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if ($filters['q'] !== '' || $filters['category'] || $filters['status'])
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card">
        @if ($products->isEmpty())
            @if ($filters['q'] !== '' || $filters['category'] || $filters['status'])
                <x-empty-state title="No matching products" message="Try a different search term or clear the filters." />
            @else
                <x-empty-state title="No products yet" message="Register your first product to start creating batches.">
                    @can('create', App\Models\Product::class)
                        <a href="{{ route('products.create') }}" class="btn btn-primary">Register product</a>
                    @endcan
                </x-empty-state>
            @endif
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th scope="col" class="table-header">Code</th>
                            <th scope="col" class="table-header">Name</th>
                            <th scope="col" class="table-header">Category</th>
                            <th scope="col" class="table-header">Manufacturer</th>
                            <th scope="col" class="table-header text-right">Batches</th>
                            <th scope="col" class="table-header">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach ($products as $product)
                            <tr class="hover:bg-ink-50">
                                <td class="table-cell"><a href="{{ route('products.show', $product) }}" class="link font-mono">{{ $product->product_code }}</a></td>
                                <td class="table-cell font-medium text-ink-900">{{ $product->name }}</td>
                                <td class="table-cell">{{ $product->category->label() }}</td>
                                <td class="table-cell">{{ $product->manufacturer_name }}</td>
                                <td class="table-cell text-right tabular-nums">{{ number_format($product->batches_count) }}</td>
                                <td class="table-cell"><x-product-status :active="$product->is_active" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="border-t border-ink-200 px-4 py-3">{{ $products->links() }}</div>
            @endif
        @endif
    </div>
</x-layouts.app>
