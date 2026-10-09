<?php

namespace App\Http\Requests;

use App\Models\Shipment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CancelShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Shipment $shipment */
        $shipment = $this->route('shipment');

        return $this->user()->can('cancel', $shipment);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'cancellation_reason' => 'reason for cancelling',
        ];
    }
}
