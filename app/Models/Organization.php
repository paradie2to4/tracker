<?php

namespace App\Models;

use App\Enums\OrganizationType;
use App\Models\Concerns\Auditable;
use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A business in the supply chain (manufacturer, distributor, retailer...).
 * Only administrators can create or edit organisations, so is_active can
 * safely be part of the edit form.
 */
#[Fillable(['name', 'type', 'tin', 'contact_email', 'contact_phone', 'is_active'])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use Auditable, HasFactory;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => OrganizationType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Location, $this>
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }
}
