<?php

namespace App\Http\Requests\Shows;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:265',
            'page' => 'nullable|integer|min:0',
            'size' => 'nullable|integer|min:1|max:100',
            'sortField' => ['nullable', Rule::in([
                'id',
                'id_integration',
                'name',
                'language',
                'status',
                'runtime',
                'average_runtime',
                'rating',
                'created_at',
                'updated_at',
            ])],
            'sortOrder' => ['nullable', Rule::in(['ASC', 'DESC', 'asc', 'desc'])],
        ];
    }
}
