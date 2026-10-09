<?php

namespace Tests\Feature;

use App\Enums\LocationType;
use App\Enums\OrganizationType;
use App\Models\Location;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplyChainTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_administrators_can_register_an_organisation_and_its_locations(): void
    {
        $this->actingAs($this->admin)
            ->post(route('organizations.store'), [
                'name' => 'Kigali Mills Ltd',
                'type' => OrganizationType::Manufacturer->value,
                'tin' => '102 345 678',
                'contact_email' => 'info@kigalimills.rw',
                'contact_phone' => '+250 788 123 456',
            ])
            ->assertRedirect();

        $organization = Organization::sole();
        $this->assertSame('102345678', $organization->tin);

        $this->actingAs($this->admin)
            ->post(route('organizations.locations.store', $organization), [
                'code' => 'kgl-plant-01',
                'name' => 'Masoro plant',
                'type' => LocationType::Factory->value,
                'district' => 'Gasabo',
            ])
            ->assertRedirect(route('locations.show', Location::sole()));

        $location = Location::sole();
        $this->assertSame('KGL-PLANT-01', $location->code);
        $this->assertTrue($location->organization->is($organization));
        $this->assertTrue($location->is_active);
    }

    public function test_staff_can_view_but_not_change_supply_chain_records(): void
    {
        $staff = User::factory()->create();
        $location = Location::factory()->create();

        $this->actingAs($staff)->get(route('organizations.index'))->assertOk();
        $this->actingAs($staff)->get(route('organizations.show', $location->organization))->assertOk();
        $this->actingAs($staff)->get(route('locations.show', $location))->assertOk();

        $this->actingAs($staff)->get(route('organizations.create'))->assertForbidden();
        $this->actingAs($staff)->post(route('organizations.store'), ['name' => 'X', 'type' => 'retailer'])->assertForbidden();
        $this->actingAs($staff)->get(route('organizations.locations.create', $location->organization))->assertForbidden();
        $this->actingAs($staff)->put(route('locations.update', $location), [
            'code' => $location->code, 'name' => 'Renamed', 'type' => 'store', 'is_active' => '0',
        ])->assertForbidden();

        $this->assertTrue($location->fresh()->is_active);
    }

    public function test_organisation_details_are_validated(): void
    {
        Organization::factory()->create(['name' => 'Existing Ltd', 'tin' => '111111111']);

        $this->actingAs($this->admin)
            ->post(route('organizations.store'), [
                'name' => 'Existing Ltd',
                'type' => 'not-a-type',
                'tin' => '12345',
                'contact_email' => 'not-an-email',
            ])
            ->assertSessionHasErrors(['name', 'type', 'tin', 'contact_email']);

        $this->actingAs($this->admin)
            ->post(route('organizations.store'), ['name' => 'New Ltd', 'type' => 'retailer', 'tin' => '111111111'])
            ->assertSessionHasErrors(['tin' => 'An organisation with this TIN is already registered.']);
    }

    public function test_location_codes_are_unique_and_districts_must_be_real(): void
    {
        $organization = Organization::factory()->create();
        Location::factory()->for($organization)->create(['code' => 'KGL-WH-01']);

        $this->actingAs($this->admin)
            ->post(route('organizations.locations.store', $organization), [
                'code' => 'kgl-wh-01',
                'name' => 'Duplicate',
                'type' => LocationType::Warehouse->value,
                'district' => 'Atlantis',
            ])
            ->assertSessionHasErrors(['code', 'district']);
    }

    public function test_a_location_can_be_deactivated(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('locations.update', $location), [
                'code' => $location->code,
                'name' => $location->name,
                'type' => $location->type->value,
                'is_active' => '0',
            ])
            ->assertRedirect(route('locations.show', $location));

        $this->assertFalse($location->fresh()->is_active);
    }

    public function test_organisations_and_locations_cannot_be_deleted(): void
    {
        $location = Location::factory()->create();

        $this->actingAs($this->admin)->delete("/organizations/{$location->organization_id}")->assertMethodNotAllowed();
        $this->actingAs($this->admin)->delete("/locations/{$location->id}")->assertMethodNotAllowed();
    }
}
