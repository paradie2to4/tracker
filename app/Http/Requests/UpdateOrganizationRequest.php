<?php

namespace App\Http\Requests;

use App\Models\Organization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends StoreOrganizationRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->organization());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'name' => ['required', 'string', 'max:150', Rule::unique('organizations', 'name')->ignore($this->organization())],
            'tin' => ['nullable', 'digits:9', Rule::unique('organizations', 'tin')->ignore($this->organization())],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function organization(): Organization
    {
        return $this->route('organization');
    }
}
