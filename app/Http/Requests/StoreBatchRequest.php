<?php

namespace App\Http\Requests;

use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreBatchRequest extends FormRequest
{
    /**
     * Largest value that fits in NUMERIC(14,3).
     */
    public const MAX_QUANTITY = '99999999999.999';

    public function authorize(): bool
    {
        return $this->user()->can('create', Batch::class);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('batch_number'))) {
            $this->merge(['batch_number' => Str::upper($this->input('batch_number'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Batches can only be registered for active products.
            'product_id' => ['required', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            // Where the batch was produced; its initial stock is placed here.
            'origin_location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'batch_number' => ['required', 'string', 'max:50', 'regex:'.StoreProductRequest::CODE_PATTERN, Rule::unique('batches', 'batch_number')],
            // Future production planning is not supported yet.
            'manufacturing_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today'],
            'expiry_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:manufacturing_date'],
            'initial_quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:'.self::MAX_QUANTITY],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.exists' => 'Select an active product.',
            'origin_location_id.exists' => 'Select an active production location.',
            'batch_number.regex' => 'The batch number may only contain letters, numbers, dots, dashes and underscores, and must start with a letter or number.',
            'batch_number.unique' => 'A batch with this number already exists.',
            'manufacturing_date.before_or_equal' => 'The manufacturing date cannot be in the future.',
            'expiry_date.after_or_equal' => 'The expiry date cannot be earlier than the manufacturing date.',
            'initial_quantity.gt' => 'The initial quantity must be greater than zero.',
            'initial_quantity.decimal' => 'Quantities may have at most 3 decimal places.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'product',
            'origin_location_id' => 'production location',
            'initial_quantity' => 'initial quantity',
            'current_quantity' => 'current quantity',
            'manufacturing_date' => 'manufacturing date',
            'expiry_date' => 'expiry date',
        ];
    }
}
