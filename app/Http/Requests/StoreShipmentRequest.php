<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates the shape of a dispatch: locations exist and differ, and each
 * entered quantity is a well-formed positive decimal. Whether enough stock
 * exists is checked by DispatchShipment under a row lock, because only
 * then is the answer guaranteed to still be true when the stock moves.
 *
 * Input: items[<batch_id>][quantity]; empty quantities are ignored.
 */
class StoreShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Shipment::class);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('is_active', true)],
            'to_location_id' => ['required', 'integer', 'different:from_location_id', Rule::exists('locations', 'id')->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['nullable', 'numeric', 'decimal:0,3', 'gt:0', 'max:'.StoreBatchRequest::MAX_QUANTITY],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                foreach (array_keys((array) $this->input('items', [])) as $key) {
                    if (filter_var($key, FILTER_VALIDATE_INT) === false) {
                        $validator->errors()->add('items', 'The shipment contains an invalid batch.');

                        return;
                    }
                }

                if (! $validator->errors()->has('items.*') && $this->quantities() === []) {
                    $validator->errors()->add('items', 'Enter a quantity for at least one batch.');
                }
            },
        ];
    }

    /**
     * Non-empty quantities keyed by batch ID.
     *
     * @return array<int, string>
     */
    public function quantities(): array
    {
        return collect((array) $this->input('items', []))
            ->map(fn ($item) => is_array($item) ? trim((string) ($item['quantity'] ?? '')) : '')
            ->filter(fn (string $quantity) => $quantity !== '')
            ->mapWithKeys(fn (string $quantity, $batchId) => [(int) $batchId => $quantity])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'to_location_id.different' => 'The destination must be different from the origin.',
            'from_location_id.exists' => 'Select an active origin location.',
            'to_location_id.exists' => 'Select an active destination location.',
            'items.*.quantity.gt' => 'The quantity must be greater than zero.',
            'items.*.quantity.decimal' => 'Quantities may have at most 3 decimal places.',
            'items.*.quantity.numeric' => 'The quantity must be a number.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'from_location_id' => 'origin',
            'to_location_id' => 'destination',
        ];
    }
}
