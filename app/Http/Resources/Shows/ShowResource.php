<?php

namespace App\Http\Resources\Shows;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="ShowDTO",
 *     description="Response da sincronização de TV shows",
 *     @OA\Property(property="id", type="string", description="Id do tv show"),
 *     @OA\Property(property="idIntegration", type="integer", description="Id da integração TVMaze"),
 *     @OA\Property(property="name", type="string", description="Nome do tv show"),
 *     @OA\Property(property="type", type="string", nullable=true),
 *     @OA\Property(property="language", type="string", nullable=true),
 *     @OA\Property(property="status", type="string", nullable=true),
 *     @OA\Property(property="runtime", type="integer", nullable=true),
 *     @OA\Property(property="averageRuntime", type="integer", nullable=true),
 *     @OA\Property(property="officialSite", type="string", nullable=true),
 *     @OA\Property(property="rating", type="number", format="float", nullable=true),
 *     @OA\Property(property="summary", type="string", nullable=true),
 *     @OA\Property(property="episodesCount", type="integer", nullable=true),
 *     @OA\Property(
 *         property="episodes",
 *         type="array",
 *         @OA\Items(ref="#/components/schemas/EpisodeDTO")
 *     )
 * )
 */
class ShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'idIntegration' => $this->id_integration,
            'name' => $this->name,
            'type' => $this->type,
            'language' => $this->language,
            'status' => $this->status,
            'runtime' => $this->runtime,
            'averageRuntime' => $this->average_runtime,
            'officialSite' => $this->official_site,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'summary' => $this->summary,
            'episodesCount' => $this->when(isset($this->episodes_count), (int) $this->episodes_count),
            'episodes' => $this->whenLoaded(
                'episodes',
                fn () => $this->episodes
                    ->map(fn ($episode) => (new EpisodeResource($episode))->toArray($request))
                    ->values()
                    ->all(),
            ),
        ];
    }
}
