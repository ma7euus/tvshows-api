<?php

namespace App\Http\Controllers\Shows;


use App\Http\Controllers\Controller;
use App\Http\Resources\TvMazeImportResource;
use App\Models\TvMazeImport;
use App\Modules\Shows\Application\Shows\UseCases\SchedulePaginatedShowImportUseCase;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Show Imports", description="Importações assíncronas de shows via TVMaze")
 */
class ShowImportController extends Controller
{
    public function __construct(
        private readonly SchedulePaginatedShowImportUseCase $schedulePaginatedShowImportUseCase,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/shows/imports/paginated",
     *     summary="Agenda a importação paginada de todos os shows da TVMaze",
     *     tags={"Show Imports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=202, description="Importação agendada", @OA\JsonContent(ref="#/components/schemas/TvMazeImportDTO")),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=409, description="Já existe uma importação em andamento", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function store(): JsonResponse
    {
        $import = $this->schedulePaginatedShowImportUseCase->execute();

        return response()->json(new TvMazeImportResource($import), 202);
    }

    /**
     * @OA\Get(
     *     path="/api/shows/imports/{id}",
     *     summary="Consulta o status da importação paginada da TVMaze",
     *     tags={"Show Imports"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, description="Id da importação", @OA\Schema(type="string", format="uuid")),
     *     @OA\Response(response=200, description="Status consultado com sucesso", @OA\JsonContent(ref="#/components/schemas/TvMazeImportDTO")),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=404, description="Importação não encontrada", @OA\JsonContent(ref="#/components/schemas/ApiError"))
     * )
     */
    public function show(TvMazeImport $import): JsonResponse
    {
        return response()->json(new TvMazeImportResource($import));
    }
}
