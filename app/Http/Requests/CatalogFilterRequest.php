<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CatalogFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:120'],
            'price_min' => ['nullable', 'numeric', 'min:0'],
            'price_max' => ['nullable', 'numeric', 'min:0'],
            'bp_min' => ['nullable', 'numeric', 'min:0'],
            'ovr_min' => ['nullable', 'integer', 'min:0', 'max:200'],
            'status' => ['nullable', Rule::in([ProductStatus::Available->value, ProductStatus::Sold->value])],
            'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['price_min', 'price_max', 'bp_min'] as $field) {
            if (filled($this->input($field))) {
                $this->merge([$field => preg_replace('/\D/', '', (string) $this->input($field))]);
            }
        }

        $sort = $this->string('sort')->toString();

        if (! in_array($sort, ['newest', 'price_asc', 'price_desc'], true)) {
            $this->merge(['sort' => 'newest']);
        }
    }
}
