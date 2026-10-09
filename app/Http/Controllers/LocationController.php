<?php

namespace App\Http\Controllers;

use App\Enums\LocationType;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Models\Organization;
use App\Models\StockMovement;
use App\Support\PageSize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Shallow nested resource: locations are created under an organisation
 * (/organizations/{organization}/locations/create) but viewed and edited
 * directly (/locations/{location}).
 */
class LocationController extends Controller
{
    public function create(Organization $organization): View
    {
        Gate::authorize('create', Location::class);

        return view('locations.create', [
            'organization' => $organization,
            'location' => new Location,
            'types' => LocationType::cases(),
            'districts' => config('productsphere.districts'),
        ]);
    }

    public function store(StoreLocationRequest $request, Organization $organization): RedirectResponse
    {
        $location = $organization->locations()->create($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('success', "Location {$location->code} has been added.");
    }

    public function show(Location $location): View
    {
        Gate::authorize('view', $location);

        $location->load('organization');

        return view('locations.show', [
            'location' => $location,
            'balances' => $location->stockBalances()
                ->where('quantity', '>', 0)
                ->with('batch.product')
                ->orderBy('batch_id')
                ->paginate(PageSize::for(request(), PageSize::NESTED), pageName: 'stock'),
            'movements' => StockMovement::query()
                ->where(fn ($query) => $query
                    ->where('from_location_id', $location->getKey())
                    ->orWhere('to_location_id', $location->getKey()))
                ->with(['batch.product', 'fromLocation', 'toLocation', 'shipment.fromLocation', 'shipment.toLocation', 'user'])
                ->latest('occurred_at')
                ->latest('id')
                ->limit(20)
                ->get(),
        ]);
    }

    public function edit(Location $location): View
    {
        Gate::authorize('update', $location);

        $location->load('organization');

        return view('locations.edit', [
            'location' => $location,
            'organization' => $location->organization,
            'types' => LocationType::cases(),
            'districts' => config('productsphere.districts'),
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $location->update($request->validated());

        return redirect()
            ->route('locations.show', $location)
            ->with('success', 'Location details have been updated.');
    }
}
