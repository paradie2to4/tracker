<?php

namespace App\Http\Requests;

use App\Enums\RemovalReason;
use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecordStockRemovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Batch $batch */
        $batch = $this->route('batch');

        return $this->user()->can('removeStock', $batch);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'location_id' => ['required', 'integer', Rule::exists('locations', 'id')],
            'quantity' => ['required', 'numeric', 'decimal:0,3', 'gt:0', 'max:'.StoreBatchRequest::MAX_QUANTITY],
            'reason' => ['required', Rule::enum(RemovalReason::class)],
            // Losses and corrections must be explained.
            'notes' => ['nullable', 'required_if:reason,lost,correction', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.gt' => 'The quantity must be greater than zero.',
            'quantity.decimal' => 'Quantities may have at most 3 decimal places.',
            'notes.required_if' => 'Explain what happened when recording a loss or a stock correction.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'location_id' => 'location',
        ];
    }
}
