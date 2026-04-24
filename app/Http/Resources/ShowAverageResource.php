<?php

namespace App\Http\Resources;

/**
 * @OA\Schema(
 *     schema="ShowAverageDTO",
 *     description="Response da média de rating por temporada",
 *     @OA\Property(property="id", type="string", description="Id do tv show"),
 *     @OA\Property(property="name", type="string", description="Nome do tv show"),
 *     @OA\Property(property="rating", type="number", format="float", description="Nota média")
 * )
 */
class ShowAverageResource
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $rating,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'rating' => $this->rating,
        ];
    }
}
