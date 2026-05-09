<?php

namespace App\Http\Resources\Shows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="SeasonAverageDTO",
 *     description="Média de rating por temporada",
 *     @OA\Property(property="season", type="integer", description="Temporada"),
 *     @OA\Property(property="averageRating", type="number", format="float", description="Nota média")
 * )
 */
class SeasonAverageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'season' => $this->season,
            'averageRating' => $this->averageRating,
        ];
    }
}
