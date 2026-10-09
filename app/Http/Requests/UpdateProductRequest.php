<?php

namespace App\Http\Requests;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateProductRequest extends StoreProductRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->product());
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'product_code' => [
                'required', 'string', 'max:50', 'regex:'.self::CODE_PATTERN,
                Rule::unique('products', 'product_code')->ignore($this->product()),
            ],
        ];
    }

    /**
     * Once batches exist, the product code may already be printed on
     * packaging and documents, so it becomes immutable.
     *
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $product = $this->product();

                if ($this->input('product_code') !== $product->product_code && $product->batches()->exists()) {
                    $validator->errors()->add(
                        'product_code',
                        'The product code cannot be changed because batches have already been registered for this product.',
                    );
                }
            },
        ];
    }

    private function product(): Product
    {
        return $this->route('product');
    }
}
