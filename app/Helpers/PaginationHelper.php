<?php

namespace App\Helpers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Classe utilitária para paginação.
 * Equivalente ao Util.java do projeto Spring Boot.
 */
class PaginationHelper
{
    /**
     * Converte um LengthAwarePaginator para o formato padronizado de resposta.
     *
     * @param LengthAwarePaginator $paginator
     * @return array{items: array, total: int, page: int, size: int}
     */
    public static function formatPageResult(LengthAwarePaginator $paginator): array
    {
        return [
            'items' => $paginator->items(),
            'total' => $paginator->total(),
            'page' => $paginator->currentPage() - 1, // zero-indexed como no Java
            'size' => $paginator->perPage(),
        ];
    }
}
