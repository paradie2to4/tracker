<?php

namespace App\Http\Requests;

use App\Enums\LocationType;
use App\Models\Location;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Location::class);
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('code'))) {
            $this->merge(['code' => Str::upper($this->input('code'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'regex:'.StoreProductRequest::CODE_PATTERN, Rule::unique('locations', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'type' => ['required', Rule::enum(LocationType::class)],
            'district' => ['nullable', Rule::in(Arr::flatten(config('productsphere.districts')))],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => 'The location code may only contain letters, numbers, dots, dashes and underscores, and must start with a letter or number.',
            'code.unique' => 'A location with this code already exists.',
            'district.in' => 'Select one of Rwanda\'s 30 districts.',
        ];
    }
}
