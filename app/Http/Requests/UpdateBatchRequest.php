<?php

namespace App\Http\Requests;

use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Only the dates of a batch can be corrected after registration.
 *
 * - The product, batch number and origin are permanent identifiers.
 * - Quantities are maintained exclusively by stock movements (shipments,
 *   removals), so the ledger always explains the current balance. They are
 *   absent from these rules, so validated() never contains them even if a
 *   client submits them.
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
        ];
    }
}
