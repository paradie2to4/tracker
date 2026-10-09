<?php

namespace App\Http\Requests;

use App\Models\Batch;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RecallBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var Batch $batch */
        $batch = $this->route('batch');

        return $this->user()->can('recall', $batch);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'recall_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'recall_reason' => 'recall reason',
        ];
    }
}
