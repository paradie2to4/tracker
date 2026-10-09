<?php

namespace App\Models;

use App\Enums\LocationType;
use App\Models\Concerns\Auditable;
use Database\Factories\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A physical place where stock is held: factory, warehouse, shop...
 * organization_id is set through $organization->locations()->create().
 */
#[Fillable(['code', 'name', 'type', 'district', 'address', 'is_active'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
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
            'type' => LocationType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Organization, $this>
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return HasMany<StockBalance, $this>
     */
    public function stockBalances(): HasMany
    {
        return $this->hasMany(StockBalance::class);
    }

    public function label(): string
    {
        return "{$this->code} — {$this->name}";
    }
}
