<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="EpisodeDTO",
 *     description="Objeto da representação de Episódios",
 *     @OA\Property(property="id", type="string", description="Id"),
 *     @OA\Property(property="name", type="string", description="Nome"),
 *     @OA\Property(property="season", type="integer", description="Temporada"),
 *     @OA\Property(property="number", type="integer", description="Episódio")
 * )
 */
class EpisodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'season' => $this->season,
            'number' => $this->number,
        ];
    }
}
