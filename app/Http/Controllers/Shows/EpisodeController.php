<?php

namespace App\Http\Controllers\Shows;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shows\EpisodeAverageRequest;
use App\Http\Resources\Shows\ShowAverageResource;
use App\Modules\Shows\Application\Shows\UseCases\GetShowSeasonAveragesUseCase;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Episodes", description="Consultas agregadas de episódios")
 */
class EpisodeController extends Controller
{
    public function __construct(
        private readonly GetShowSeasonAveragesUseCase $getShowSeasonAveragesUseCase,
    ) {}

    /**
     * @OA\Get(
     *     path="/api/episodes/average",
     *     summary="Calcula a média de rating dos episódios por temporada para um show",
     *     tags={"Episodes"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="showId", in="query", required=true, description="Id do show sincronizado"),
     *     @OA\Response(response=200, description="Médias calculadas com sucesso", @OA\JsonContent(ref="#/components/schemas/ShowAverageDTO")),
     *     @OA\Response(response=422, description="Show sem episódios para cálculo")
     * )
     */
    public function average(EpisodeAverageRequest $request): JsonResponse
    {
        $result = $this->getShowSeasonAveragesUseCase->execute($request->validated('showId'));
        //$result = $this->getShowSeasonAveragesUseCase->calculate($request->validated('showId'));

        return response()->json(new ShowAverageResource($result));
    }
}
