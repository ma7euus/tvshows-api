<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ShowDTO",
 *     description="Response da sincronização de TV shows",
 *     @OA\Property(property="id", type="string", description="Id do tv show"),
 *     @OA\Property(property="name", type="string", description="Nome do tv show")
 * )
 */
class ShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
