<?php

namespace App\Exceptions;

use RuntimeException;

class AlreadyExistsException extends RuntimeException
{
    public function __construct(string $entity, string $identifier)
    {
        parent::__construct("{$entity} already exists: {$identifier}");
    }
}
