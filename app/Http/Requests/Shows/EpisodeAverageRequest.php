<?php

namespace App\Http\Requests\Shows;

use Illuminate\Foundation\Http\FormRequest;

class EpisodeAverageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'showId' => 'required|uuid',
        ];
    }
}
