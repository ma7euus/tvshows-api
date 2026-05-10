<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => 'nullable|string|max:100',
            'page' => 'nullable|integer|min:0',
            'size' => 'nullable|integer|min:1|max:100',
            'sortField' => ['nullable', Rule::in(['id', 'username', 'role', 'enabled', 'created_at', 'updated_at'])],
            'sortOrder' => ['nullable', Rule::in(['ASC', 'DESC', 'asc', 'desc'])],
        ];
    }
}
