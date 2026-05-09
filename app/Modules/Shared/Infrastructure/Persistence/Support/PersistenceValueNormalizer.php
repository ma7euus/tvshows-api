<?php

namespace App\Modules\Shared\Infrastructure\Persistence\Support;

final class PersistenceValueNormalizer
{
    public function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        return $normalized === '' ? null : $normalized;
    }
}
