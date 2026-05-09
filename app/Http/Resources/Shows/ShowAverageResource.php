<?php

namespace App\Http\Resources\Shows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ShowAverageDTO",
 *     description="Média de rating por temporada para um show",
 *     @OA\Property(property="showId", type="string", description="Id do show"),
 *     @OA\Property(property="showName", type="string", description="Nome do show"),
 *     @OA\Property(
 *         property="averages",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/SeasonAverageDTO")
 *     )
 * )
 */
class ShowAverageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'showId' => $this->show->id,
            'showName' => $this->show->name,
            'averages' => collect($this->averages)
                ->map(fn ($average) => (new SeasonAverageResource($average))->toArray($request))
                ->values()
                ->all(),
        ];
    }
}
