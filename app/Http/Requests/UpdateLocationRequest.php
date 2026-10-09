<?php

namespace App\Http\Requests;

use App\Models\Location;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateLocationRequest extends StoreLocationRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->location());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'code' => ['required', 'string', 'max:50', 'regex:'.StoreProductRequest::CODE_PATTERN, Rule::unique('locations', 'code')->ignore($this->location())],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function location(): Location
    {
        return $this->route('location');
    }
}
