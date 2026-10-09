<?php

namespace App\Http\Requests;

use App\Enums\OrganizationType;
use App\Models\Organization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Organization::class);
    }

    /**
     * Accept TINs typed with spaces or dashes, e.g. "100 234 567".
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('tin'))) {
            $this->merge(['tin' => preg_replace('/[\s-]+/', '', $this->input('tin'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', Rule::unique('organizations', 'name')],
            'type' => ['required', Rule::enum(OrganizationType::class)],
            'tin' => ['nullable', 'digits:9', Rule::unique('organizations', 'tin')],
            'contact_email' => ['nullable', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9 ()-]{7,30}$/'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tin.digits' => 'The TIN must be exactly 9 digits.',
            'tin.unique' => 'An organisation with this TIN is already registered.',
            'name.unique' => 'An organisation with this name already exists.',
            'contact_phone.regex' => 'Enter a valid phone number, e.g. +250 788 123 456.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tin' => 'TIN',
            'contact_email' => 'contact email',
            'contact_phone' => 'contact phone',
        ];
    }
}
