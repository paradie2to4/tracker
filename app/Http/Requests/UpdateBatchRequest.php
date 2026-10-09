<?php

namespace App\Http\Requests;

use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * The product and batch number are identifiers and cannot be changed after
 * creation, so they are simply absent from these rules: validated() will
 * never contain them, even if a client submits them.
 *
 * current_quantity is editable during the MVP only. Once stock movements
 * exist (Phase 6) it will be maintained exclusively by movement records.
 */
class UpdateBatchRequest extends StoreBatchRequest
{
    public function authorize(): bool
    {
        /** @var Batch $batch */
        $batch = $this->route('batch');

        return $this->user()->can('update', $batch);
    }

    protected function prepareForValidation(): void
    {
        //
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = parent::rules();

        return [
            'manufacturing_date' => $rules['manufacturing_date'],
            'expiry_date' => $rules['expiry_date'],
            'initial_quantity' => $rules['initial_quantity'],
            'current_quantity' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'lte:initial_quantity'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...parent::messages(),
            'current_quantity.min' => 'The current quantity cannot be negative.',
            'current_quantity.lte' => 'The current quantity cannot exceed the initial quantity.',
            'current_quantity.decimal' => 'Quantities may have at most 3 decimal places.',
        ];
    }
}
