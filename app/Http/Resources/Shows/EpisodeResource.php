<?php

namespace App\Http\Resources\Shows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="EpisodeDTO",
 *     description="Objeto da representação de Episódios",
 *     @OA\Property(property="id", type="string", description="Id"),
 *     @OA\Property(property="idIntegration", type="integer", description="Id da integração TVMaze"),
 *     @OA\Property(property="name", type="string", description="Nome"),
 *     @OA\Property(property="season", type="integer", description="Temporada"),
 *     @OA\Property(property="number", type="integer", description="Episódio"),
 *     @OA\Property(property="type", type="string", nullable=true),
 *     @OA\Property(property="airdate", type="string", format="date", nullable=true),
 *     @OA\Property(property="airtime", type="string", nullable=true),
 *     @OA\Property(property="airstamp", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="runtime", type="integer", nullable=true),
 *     @OA\Property(property="rating", type="number", format="float", nullable=true),
 *     @OA\Property(property="summary", type="string", nullable=true)
 * )
 */
class EpisodeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'idIntegration' => $this->id_integration,
            'name' => $this->name,
            'season' => $this->season,
            'number' => $this->number,
            'type' => $this->type,
            'airdate' => optional($this->airdate)->toDateString(),
            'airtime' => $this->airtime,
            'airstamp' => optional($this->airstamp)->toIso8601String(),
            'runtime' => $this->runtime,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'summary' => $this->summary,
        ];
    }
}
