<?php

namespace App\Models;

use App\Enums\BatchStatus;
use Database\Factories\BatchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Recall columns are not fillable: they are only written by the recall action.
#[Fillable(['product_id', 'batch_number', 'manufacturing_date', 'expiry_date', 'initial_quantity', 'current_quantity'])]
class Batch extends Model
{
    /** @use HasFactory<BatchFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'manufacturing_date' => 'date',
            'expiry_date' => 'date',
            // decimal:3 casts to a string such as "1500.250", never a float.
            'initial_quantity' => 'decimal:3',
            'current_quantity' => 'decimal:3',
            'recalled_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recalledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recalled_by');
    }

    /**
     * The effective status, derived from stored facts (see BatchStatus).
     *
     * Must stay consistent with scopeWithStatus(), which expresses the same
     * rules in SQL so lists can be filtered and paginated in the database.
     *
     * @return Attribute<BatchStatus, never>
     */
    protected function status(): Attribute
    {
        return Attribute::get(function (): BatchStatus {
            if ($this->recalled_at !== null) {
                return BatchStatus::Recalled;
            }

            if ($this->isEmpty()) {
                return BatchStatus::Depleted;
            }

            if ($this->expiry_date !== null && $this->expiry_date->lt(today())) {
                return BatchStatus::Expired;
            }

            return BatchStatus::Active;
        });
    }

    public function isEmpty(): bool
    {
        // "0.000" -> "" once the dot and zeros are removed. Working on the
        // exact decimal string avoids any float conversion.
        return ltrim(str_replace('.', '', (string) $this->current_quantity), '0') === '';
    }

    public function isRecalled(): bool
    {
        return $this->recalled_at !== null;
    }

    /**
     * Whether an active batch expires within the configured warning window.
     */
    public function isApproachingExpiry(): bool
    {
        return $this->status === BatchStatus::Active
            && $this->expiry_date !== null
            && $this->expiry_date->lte(today()->addDays(config('productsphere.expiry_warning_days')));
    }

    /**
     * Number of whole days until expiry (negative once expired).
     */
    public function daysUntilExpiry(): ?int
    {
        return $this->expiry_date === null
            ? null
            : (int) today()->diffInDays($this->expiry_date, false);
    }

    /**
     * Filter by effective status, using the same precedence as status().
     *
     * @param  Builder<Batch>  $query
     */
    public function scopeWithStatus(Builder $query, BatchStatus $status): void
    {
        $today = today()->toDateString();

        match ($status) {
            BatchStatus::Recalled => $query->whereNotNull('recalled_at'),

            BatchStatus::Depleted => $query
                ->whereNull('recalled_at')
                ->where('current_quantity', '<=', 0),

            BatchStatus::Expired => $query
                ->whereNull('recalled_at')
                ->where('current_quantity', '>', 0)
                ->whereNotNull('expiry_date')
                ->where('expiry_date', '<', $today),

            BatchStatus::Active => $query
                ->whereNull('recalled_at')
                ->where('current_quantity', '>', 0)
                ->where(fn (Builder $query) => $query
                    ->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', $today)),
        };
    }

    /**
     * Active batches that expire between today and today + $days (inclusive).
     *
     * @param  Builder<Batch>  $query
     */
    public function scopeExpiringWithin(Builder $query, int $days): void
    {
        $query->withStatus(BatchStatus::Active)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [
                today()->toDateString(),
                today()->addDays($days)->toDateString(),
            ]);
    }
}
