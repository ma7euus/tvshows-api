<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="TvMazeImportDTO",
 *     description="Status de uma importação paginada da TVMaze",
 *     @OA\Property(property="id", type="string", format="uuid"),
 *     @OA\Property(property="status", type="string", enum={"pending","running","complete","error"}),
 *     @OA\Property(property="currentPage", type="integer"),
 *     @OA\Property(property="processedPages", type="integer"),
 *     @OA\Property(property="processedShows", type="integer"),
 *     @OA\Property(property="errorMessage", type="string", nullable=true),
 *     @OA\Property(property="monitorUrl", type="string", format="uri"),
 *     @OA\Property(property="startedAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="finishedAt", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="createdAt", type="string", format="date-time"),
 *     @OA\Property(property="updatedAt", type="string", format="date-time")
 * )
 */
class TvMazeImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status->value,
            'currentPage' => (int) $this->current_page,
            'processedPages' => (int) $this->processed_pages,
            'processedShows' => (int) $this->processed_shows,
            'errorMessage' => $this->error_message,
            'monitorUrl' => route('shows.imports.show', $this->id),
            'startedAt' => $this->started_at?->toIso8601String(),
            'finishedAt' => $this->finished_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
