<?php

namespace App\Http\Controllers;

use App\Enums\OrganizationType;
use App\Http\Requests\StoreOrganizationRequest;
use App\Http\Requests\UpdateOrganizationRequest;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Organization::class);

        $filters = [
            'q' => trim((string) $request->query('q', '')),
            'type' => OrganizationType::tryFrom((string) $request->query('type')),
        ];

        $organizations = Organization::query()
            ->withCount('locations')
            ->when($filters['q'] !== '', fn ($query) => $query->where(fn ($query) => $query
                ->whereLike('name', "%{$filters['q']}%")
                ->orWhereLike('tin', "%{$filters['q']}%")))
            ->when($filters['type'], fn ($query, OrganizationType $type) => $query->where('type', $type))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('organizations.index', [
            'organizations' => $organizations,
            'filters' => $filters,
            'types' => OrganizationType::cases(),
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Organization::class);

        return view('organizations.create', [
            'organization' => new Organization,
            'types' => OrganizationType::cases(),
        ]);
    }

    public function store(StoreOrganizationRequest $request): RedirectResponse
    {
        $organization = Organization::create($request->validated());

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', "{$organization->name} has been registered. Add its locations next.");
    }

    public function show(Organization $organization): View
    {
        Gate::authorize('view', $organization);

        return view('organizations.show', [
            'organization' => $organization,
            'locations' => $organization->locations()->orderBy('name')->get(),
        ]);
    }

    public function edit(Organization $organization): View
    {
        Gate::authorize('update', $organization);

        return view('organizations.edit', [
            'organization' => $organization,
            'types' => OrganizationType::cases(),
        ]);
    }

    public function update(UpdateOrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update($request->validated());

        return redirect()
            ->route('organizations.show', $organization)
            ->with('success', 'Organisation details have been updated.');
    }
}
