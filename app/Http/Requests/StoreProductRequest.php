<?php

namespace App\Http\Requests;

use App\Enums\ProductCategory;
use App\Enums\UnitOfMeasure;
use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    /**
     * Allowed characters for identifiers such as product codes.
     */
    public const CODE_PATTERN = '/^[A-Z0-9][A-Z0-9._-]*$/';

    public function authorize(): bool
    {
        return $this->user()->can('create', Product::class);
    }

    /**
     * Normalise the code so "abc-1" and "ABC-1" are treated as duplicates.
     * (Laravel's TrimStrings middleware has already trimmed whitespace.)
     */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('product_code'))) {
            $this->merge(['product_code' => Str::upper($this->input('product_code'))]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_code' => ['required', 'string', 'max:50', 'regex:'.self::CODE_PATTERN, Rule::unique('products', 'product_code')],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::enum(ProductCategory::class)],
            'manufacturer_name' => ['required', 'string', 'max:150'],
            'unit_of_measure' => ['required', Rule::enum(UnitOfMeasure::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_code.regex' => 'The product code may only contain letters, numbers, dots, dashes and underscores, and must start with a letter or number.',
            'product_code.unique' => 'A product with this code already exists.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'manufacturer_name' => 'manufacturer',
            'unit_of_measure' => 'unit of measure',
        ];
    }
}
