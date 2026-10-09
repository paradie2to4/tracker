<?php

namespace App\Models;

use App\Enums\ProductCategory;
use App\Enums\UnitOfMeasure;
use App\Models\Concerns\Auditable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// `is_active` is not fillable: activation is a separate, admin-only action
// (see ProductStatusController) rather than a field on the edit form.
#[Fillable(['product_code', 'name', 'description', 'category', 'manufacturer_name', 'unit_of_measure'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
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
            'category' => ProductCategory::class,
            'unit_of_measure' => UnitOfMeasure::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Batch, $this>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(Batch::class);
    }

    /**
     * Case-insensitive search on name or product code (ILIKE on PostgreSQL).
     *
     * @param  Builder<Product>  $query
     */
    public function scopeSearch(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $query->where(function (Builder $query) use ($term) {
            $query->whereLike('name', "%{$term}%")
                ->orWhereLike('product_code', "%{$term}%");
        });
    }
}
