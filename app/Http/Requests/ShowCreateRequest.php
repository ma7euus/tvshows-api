<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowCreateRequest",
 *     description="Payload para sincronização de um show",
 *     required={"name"},
 *     @OA\Property(property="name", type="string", minLength=1, maxLength=265, description="Nome do show")
 * )
 */
class ShowCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:265',
        ];
    }
}
