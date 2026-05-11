<?php

namespace App\Http\Controllers\Shows;


use App\Helpers\PaginationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Shows\ShowCreateRequest;
use App\Http\Requests\Shows\ShowListRequest;
use App\Http\Resources\Shows\ShowResource;
use App\Modules\Shows\Application\Shows\UseCases\ListShowsUseCase;
use App\Modules\Shows\Application\Shows\UseCases\SyncShowUseCase;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(name="Shows", description="Sincronização e consulta de shows")
 */
class ShowController extends Controller
{
    public function __construct(
        protected readonly SyncShowUseCase $syncShowUseCase,
        protected readonly ListShowsUseCase $listShowsUseCase,
    ) {}

    /**
     * @OA\Post(
     *     path="/api/shows",
     *     summary="Sincroniza um show e seus episódios a partir da API TVMaze",
     *     tags={"Shows"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/ShowCreateRequest")),
     *     @OA\Response(response=201, description="Show sincronizado com sucesso", @OA\JsonContent(ref="#/components/schemas/ShowDTO")),
     *     @OA\Response(response=200, description="Show já existente atualizado", @OA\JsonContent(ref="#/components/schemas/ShowDTO")),
     *     @OA\Response(response=403, description="Acesso negado"),
     *     @OA\Response(response=422, description="Show sem episódios disponíveis para importação"),
     *     @OA\Response(response=404, description="Show não encontrado na API externa")
     * )
     */
    public function store(ShowCreateRequest $request): JsonResponse
    {
        $result = $this->syncShowUseCase->execute($request->validated('name'));

        return response()->json(new ShowResource($result->show), $result->created ? 201 : 200);
    }

    /**
     * @OA\Get(
     *     path="/api/shows",
     *     summary="Lista shows sincronizados com paginação, filtro e ordenação",
     *     tags={"Shows"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(name="name", in="query", required=false, description="Filtro por nome"),
     *     @OA\Parameter(name="page", in="query", required=false, description="Número da página, iniciando em 0"),
     *     @OA\Parameter(name="size", in="query", required=false, description="Quantidade por página"),
     *     @OA\Parameter(
     *         name="sortField",
     *         in="query",
     *         required=false,
     *         description="Campo de ordenação",
     *         @OA\Schema(
     *             type="string",
     *             default="name",
     *             enum={"id","id_integration","name","language","status","runtime","average_runtime","rating","created_at","updated_at"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sortOrder",
     *         in="query",
     *         required=false,
     *         description="Direção da ordenação",
     *         @OA\Schema(type="string", default="ASC", enum={"ASC","DESC"})
     *     ),
     *     @OA\Response(response=200, description="Listagem realizada com sucesso")
     * )
     */
    public function index(ShowListRequest $request): JsonResponse
    {
        $paginator = $this->listShowsUseCase->execute(
            name: $request->query('name', ''),
            page: (int) $request->query('page', 0),
            size: (int) $request->query('size', 10),
            sortField: $request->query('sortField', 'name'),
            sortOrder: $request->query('sortOrder', 'ASC'),
        );

        $paginator->getCollection()->transform(fn ($show) => new ShowResource($show));

        return response()->json(PaginationHelper::formatPageResult($paginator));
    }
}
